<?php

namespace App\Http\Controllers\EmailsTemplates;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Jobs\SendQueueEmail;
use App\Jobs\SendQueueEmailCleaner;
use App\Mail\MailData;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateAttachment;
use App\Models\User;
use App\Models\UserManager;
use Auth;
use DB;
use Exception;
use File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Log;
use Str;
use Validator;

class EmailsTemplatesController extends Controller {

    public function __construct() {
        $this->middleware(function ($request, $next) {
            if ($this->isDirect($request)) {
                self::requirePermission(self::DIRECT, $this->getDirect($request));
            } else {
                self::requirePermission(self::ANY);
            }
            return $next($request);
        });
    }

    public function index(Request $request) {
        return view('layouts.app', [
            'page_title' => 'Manage Mass Mails',
            'slot' => view('components.list.list', ['key' => 'all', 'header' => $this->listHeader(), 'body' => $this->list($request)])
        ]);
    }

    public function list(Request $request) {
        $templates = EmailTemplate::page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('admin.emails-templates.list', absolute: false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Title', 'Subject', 'Message', 'Actions'],
            'rows' => $templates->getCollection()->toString(fn($template) => $this->listRow($template)->render()),
            'pagination' => $templates->links()
        ]);
    }

    private function listRow(EmailTemplate $template) {
        $edit = self::checkPermission(self::UPDATE) && !$this->isDirect();
        $delete = self::checkPermission(self::DELETE) && !$this->isDirect();
        $extra = self::checkPermission(self::SEND)
            ? view('components.list.row-action', [
                'click' => 'Ads.item.openExtra(this)',
                'title' => 'Send Email',
                'icon' => 'send',
                'url' => route('admin.emails-templates.send-form', ['template' => $template->id, 'direct' => $this->getDirect()], false)
            ])->render()
            : '';

        return view('components.list.row', [
            'id' => $template->id,
            'columns' => [$template->title, Str::limit($template->subject), Str::limit($template->message)],
            'show' => ['url' => route('admin.emails-templates.show', ['template' => $template->id], false)],
            'edit' => $edit ? ['url' => route('admin.emails-templates.edit', ['template' => $template->id], false)] : null,
            'delete' => $delete ? ['url' => route('admin.emails-templates.destroy', ['template' => $template->id], false)] : null,
            'extra' => $extra
        ]);
    }

    public function show(EmailTemplate $template) {
        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Message ID:', 'value' => $template->id],
                ['caption' => 'Title:', 'value' => $template->title],
                ['caption' => 'Subject:', 'full' => true, 'value' => $template->subject],
                ['caption' => 'Message:', 'full' => true, 'value' => '<div style="font-size:15px"><br>' . nl2br($template->message) . '</div>'],
            ]
        ]);
    }

    private function form(EmailTemplate $template = null) {
        self::requirePermission(isset($template) ? self::UPDATE : self::CREATE);

        return view('components.emailstemplates.form', compact('template'));
    }

    private function listHeader() {
        $data = ['title' => 'Existing Templates'];
        if (!$this->isDirect() && self::checkPermission(self::CREATE)) {
            $data['add'] = $this->form();
        }
        return view('components.list.header', $data);
    }

    public function edit(EmailTemplate $template) {
        self::requirePermission(self::UPDATE);

        return $this->form($template);
    }

    public function destroy(EmailTemplate $template) {
        self::requirePermission(self::DELETE);

        try {
            return DB::transaction(function () use ($template) {
                foreach ($template->attachments as $attachment) {
                    if (!$this->deleteAttachment($attachment)) {
                        return false;
                    }
                }
                return $template->delete();
            });
        } catch (Exception) {
            return false;
        }
    }

    private function deleteAttachment(EmailTemplateAttachment $attachment) {
        try {
            return DB::transaction(fn() => Storage::delete($attachment->attachment) && $attachment->delete());
        } catch (Exception $e) {
            return false;
        }
    }

    public function store(Request $request) {
        self::requirePermission(self::CREATE);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        $template = new EmailTemplate([
            'title' => $request->title,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);
        try {
            DB::transaction(function () use ($template, $request) {
                $template->save();

                if ($request->has('attachment')) {
                    foreach ($request->input('attachment') as $i => $attachment) {
                        if (!empty($attachment['name']) && $request->hasFile('attachment.' . $i . '.file')) {
                            $attach = EmailTemplateAttachment::create([
                                'email_template_id' => $template->id,
                                'name' => $attachment['name'],
                                'attachment' => '',
                                'inline' => !empty($attachment['inline']) ? 1 : 0
                            ]);
                            $path = $this->uploadAttachment($request->file('attachment.' . $i . '.file'), $attach);
                            if ($path === false) {
                                return $this->failure(['form' => 'Upload failed']);
                            }
                            $attach->update(['attachment' => $path]);
                        }
                    }
                }
                return true;
            });
            return $this->success($this->listRow($template)->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Request $request, EmailTemplate $template) {
        self::requirePermission(self::UPDATE);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        try {
            DB::transaction(function () use ($template, $request) {
                $template->update($request->all());

                $attachmentsReq = $request->input('attachment');
                if ($template->attachments) {
                    foreach ($template->attachments as $templateAttachment) {
                        $id = $templateAttachment->id;
                        if (array_key_exists($id, $attachmentsReq)) {
                            Log::error("Attachment#" . $id . " exists.");
                            Log::error("Request attachment#" . $id . " name: " . $request->input('attachment.' . $id . '.name'));
                            $templateAttachment->name = $request->input('attachment.' . $id . '.name');
                            $templateAttachment->inline = !empty($request->input('attachment.' . $id . '.inline')) ? 1 : 0;
                            if ($request->hasFile('attachment.' . $id . '.file')) {
                                if (!Storage::delete($templateAttachment->attachment)) {
                                    Log::error("Delete attachment#" . $id . " failed.");
                                    return $this->failure(['form' => 'Upload failed']);
                                }
                                $path = $this->uploadAttachment($request->file('attachment.' . $id . '.file'), $templateAttachment);
                                if ($path !== false) {
                                    $templateAttachment->attachment = $path;
                                } else {
                                    Log::error("Upload attachment#" . $id . " failed.");
                                    return $this->failure(['form' => 'Upload failed']);
                                }
                            }
                            $templateAttachment->save();
                        } else {
                            if (!$this->deleteAttachment($templateAttachment)) {
                                Log::error("Deleting attachment#" . $id . " failed.");
                                return $this->failure(['form' => 'Upload failed']);
                            }
                        }
                    }
                }

                if ($request->has('attachment')) {
                    foreach ($request->input('attachment') as $attachId => $attachment) {
                        if (!$template->attachments->contains($attachId) && !empty($attachment['name']) && $request->hasFile('attachment.' . $attachId . '.file')) {
                            $attach = EmailTemplateAttachment::create([
                                'email_template_id' => $template->id,
                                'name' => $attachment['name'],
                                'attachment' => '',
                                'inline' => !empty($attachment['inline']) ? 1 : 0
                            ]);
                            $path = $this->uploadAttachment($request->file('attachment.' . $attachId . '.file'), $attach);
                            if ($path === false) {
                                Log::error("Upload attachment#" . $attach->id . " failed.");
                                return $this->failure(['form' => 'Upload failed']);
                            }
                            $attach->update(['attachment' => $path]);
                        }
                    }
                }
                return true;
            });

            return $this->success($this->listRow($template)->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    private function uploadAttachment(UploadedFile $file, EmailTemplateAttachment $attachment): bool|string {
        $name = $attachment->id . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('attachments/' . $attachment->email_template_id, $name) !== false ? 'attachments/' . $attachment->email_template_id . '/' . $name : false;
    }

    public function sendForm(EmailTemplate $template) {
        self::requirePermission(self::DIRECT, $this->getDirect());

        $opt = fn($u, $selected = false) => Helper::option($u->id, $u->name, $selected, ['data-subtext' => $u->email]);

        $direct = $this->getDirect();
        $managers_options = $advertisers_options = $publishers_options = null;
        if (isset($direct)) {
            $user = User::find($direct);
            if ($user) {
                if ($user->isManager()) {
                    $managers_options = $opt($user, true);
                } elseif ($user->isAdvertiser()) {
                    $advertisers_options = $opt($user, true);
                } elseif ($user->isPublisher()) {
                    $publishers_options = $opt($user, true);
                }
            }
        } else {
            $managers_options = Helper::option('all', 'All Managers', false) .
                Helper::option('', '', false, ['data-divider' => "true"]) .
                User::active()->excludingMe()->asManager()->get(['id', 'name', 'email'])->toString(fn($u) => $opt($u));
            $advertisers_options = Helper::option('all', 'All Advertisers', false) .
                Helper::option('', '', false, ['data-divider' => "true"]) .
                User::active()->excludingMe()->asAdvertiser()->get(['id', 'name', 'email'])->toString(fn($u) => $opt($u));
            $publishers_options = Helper::option('all', 'All Publishers', false) .
                Helper::option('', '', false, ['data-divider' => "true"]) .
                User::active()->excludingMe()->asPublisher()->get(['id', 'name', 'email'])->toString(fn($u) => $opt($u));
        }
        return view('components.emailstemplates.send-form', compact('template', 'managers_options', 'advertisers_options', 'publishers_options', 'direct'));
    }

    public function send(EmailTemplate $template, Request $request) {
        self::requirePermission(self::DIRECT, $this->getDirect());

        $validator = Validator::make($request->all(), ['subject' => 'required|string|max:255', 'message' => 'required|string']);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        $emails = [];
        if (!$this->isDirect()) {
            if (empty($request->input('managers')) && empty($request->input('advertisers')) && empty($request->input('publishers'))
                && empty($request->input('emails')) && !$request->has('test')) {
                return $this->failure(['form' => 'No participants added.']);
            }

            $reduceFn = function ($list, $u) {
                $list[$u->email] = $u->name;
                return $list;
            };
            if (!empty($request->input('managers'))) {
                $query = User::select(['name', 'email'])->excludingMe()->asManager()->active();
                if (!in_array('all', $request->input('managers'))) {
                    $query = $query->whereIn('id', $request->input('managers'));
                }
                $emails += $query->get()->reduce($reduceFn, []);
            }
            if (!empty($request->input('advertisers'))) {
                $query = User::select(['name', 'email'])->asAdvertiser()->active();
                if (!in_array('all', $request->input('advertisers'))) {
                    $query = $query->whereIn('id', $request->input('advertisers'));
                }
                $emails += $query->get()->reduce($reduceFn, []);
            }
            if (!empty($request->input('publishers'))) {
                $query = User::select(['name', 'email'])->asPublisher()->active();
                if (!in_array('all', $request->input('publishers'))) {
                    $query = $query->whereIn('id', $request->input('publishers'));
                }
                $emails += $query->get()->reduce($reduceFn, []);
            }
            if (!empty($request->input('emails'))) {
                $emails += array_reduce(explode(";", $request->input('emails')), function ($list, $address) {
                    $address = strtolower(trim($address));
                    if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
                        $list[$address] = null;
                    }
                    return $list;
                }, []);
            }
            $emails = array_unique($emails);

        } else {
            $user = User::find($this->getDirect());
            if (isset($user)) {
                $allowed = match ($user->type) {
                    'Advertiser' => self::checkPermission(self::DIRECT_ADVERTISERS),
                    'Publisher' => self::checkPermission(self::DIRECT_PUBLISHERS),
                    'Manager' => self::isAdmin()
                };
                if ($allowed) {
                    $emails[$user->email] = $user->name;
                } else {
                    abort(422);
                }
            }
        }

        if (empty($emails) && !$request->has('test')) {
            return $this->failure(['form' => 'No valid participants entered.']);
        }

        if ($request->has('send-copy')) {
            $user = Auth::user();
            $emails[$user->email] = $user->name;
        }

        $subject = $request->input("subject");
        $body = $request->input("message");

        $mailDataWithAttachments = new MailData();
        $attachmentDir = 'emailqueue-attachments/' . md5($template->id . ',' . time() . ',' . Str::random()) . '/';
        try {
            if (!empty($request->input('template_attachments'))) {
                $attachments = $template->attachments()->whereIn('id', $request->input('template_attachments'))->get();
                foreach ($attachments as $attachment) {
                    if (!$attachment->inline || Helper::hasPhx('attach-' . $attachment->name, $body)) {
                        $ext = '.' . pathinfo(Storage::path($attachment->attachment), PATHINFO_EXTENSION);
                        $name = md5($attachment->attachment) . $ext;

                        if (!Storage::copy($attachment->attachment, $attachmentDir . $name)) {
                            throw new Exception("Copy template attachments to " . $attachmentDir . $name . " failed.");
                        }
                        if ($attachment->inline) {
                            $mailDataWithAttachments->addInlineAttachment($attachmentDir . $name, $attachment->name);
                        } else {
                            $mime = File::mimeType(Storage::path($attachmentDir . $name));
                            $mailDataWithAttachments->addAttachment($attachmentDir . $name, $attachment->name . $ext, $mime);
                        }
                    }
                }
            }

            if (!empty($request->input('attachment'))) {
                foreach ($request->input('attachment') as $i => $attachment) {
                    if (!empty($attachment['name']) && $request->hasFile('attachment.' . $i . '.file')) {
                        if (empty($attachment['inline']) || Helper::hasPhx('attach-' . $attachment['name'], $body)) {
                            $file = $request->file('attachment.' . $i . '.file');
                            $ext = '.' . $file->getClientOriginalExtension();
                            $name = md5($file->path() . $i) . $ext;
                            if ($file->storeAs($attachmentDir, $name) === false) {
                                throw new Exception("Uploading attachment " . $attachmentDir . $name . " failed.");
                            }
                            if (!empty($attachment['inline'])) {
                                $mailDataWithAttachments->addInlineAttachment($attachmentDir . $name, $attachment['name']);
                            } else {
                                $mailDataWithAttachments->addAttachment($attachmentDir . $name, $attachment['name'] . $ext, $file->getMimeType());
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            report($e);
            Storage::deleteDirectory($attachmentDir);
        }

        if ($request->has('test')) {
            $user = Auth::user();
            $emails = [$user->email => $user->name];
        }

        foreach ($emails as $email => $name) {
            $mailData = clone $mailDataWithAttachments;
            $mailData->setRecipient($email, $name);

            $mailData->setSubject(Helper::phx(['receiver_email', 'receiver_name'], [$email, $name], $subject));
            $mailData->setBody(Helper::phx(['receiver_email', 'receiver_name'], [$email, $name], $body));

            dispatch(new SendQueueEmail($mailData));
        }
        dispatch(new SendQueueEmailCleaner($attachmentDir));

        return $this->success(
            $this->listRow($template)->render(),
            view('components.page-message', [
                'class' => 'alert-success',
                'icon' => 'fa-check',
                'message' => 'Success! Emails queued for sending.'
            ])->render()
        );
    }

    private function isDirect(Request $request = null): bool {
        return ($request ?? \request())->has('direct');
    }

    private function getDirect(Request $request = null): string|null {
        return ($request ?? \request())->get('direct');
    }

    ////////////////////////////////////////////////////
    private const ANY = 'Any';
    private const DIRECT = 'Direct';
    private const DIRECT_ADVERTISERS = 'Direct Advertisers';
    private const DIRECT_PUBLISHERS = 'Direct Publishers';
    private const SEND = 'Send';
    private const CREATE = 'Create';
    private const UPDATE = 'Update';
    private const DELETE = 'Delete';

    private static function checkPermission(string $permission, int $direct = null): bool {
        if (self::isAdmin()) {
            return true;
        }

        if ($permission === self::ANY) {
            return User::hasAnyPermissions('send_email', UserManager::PERMISSIONS['send_email']);
        }
        if ($permission === self::DIRECT_ADVERTISERS) {
            return User::hasPermission('advertisers', 'Send Email')
                && User::hasPermission('send_email', 'Send');
        }
        if ($permission === self::DIRECT_PUBLISHERS) {
            return User::hasPermission('publishers', 'Send Email')
                && User::hasPermission('send_email', 'Send');
        }
        if ($permission === self::DIRECT) {
            if (self::checkPermission(self::SEND)) {
                if (isset($direct)) {
                    if (($direct = User::find($direct)) !== null) {
                        if ($direct->isAdvertiser()) {
                            return self::checkPermission(self::DIRECT_ADVERTISERS);
                        } elseif ($direct->isPublisher()) {
                            return self::checkPermission(self::DIRECT_PUBLISHERS);
                        }
                    }
                } else {
                    return true;
                }
            }
            return false;
        }

        return User::hasPermission('send_email', $permission);
    }

    private static function requirePermission(string $permission, int $direct = null) {
        if (!self::checkPermission($permission, $direct)) {
            abort(403);
        }
    }

    private static function isAdmin(): bool {
        return Auth::user()->isAdmin();
    }
}
