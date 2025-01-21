<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\TicketMessage;
use App\Models\TicketThread;
use App\Models\User;
use App\Notifications\GuestTicketReply;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Notification;
use Str;
use Validator;

class TicketsController extends Controller {
    public function createGuest() {
        return view('components.tickets.public-form');
    }

    public function createReplyGuest(TicketThread $thread, string $hash) {
        if ($hash !== $this->getReplyHash($thread)) {
            abort(403);
        }
        return view('components.tickets.public-reply-form', ['thread' => $thread, 'hash' => $hash]);
    }

    public function sendGuest(Request $request) {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'category' => 'required|in:Publishers,Advertisers,Billing,Other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'captcha' => 'required|captcha',
        ]);
        DB::transaction(function () use ($request) {
            TicketThread::create(['subject' => e($request->subject), 'category' => $request->category])
                ->messages()->save(new TicketMessage(['message' => e($request->message), 'guest' => $request->email, 'reply' => false]));
        });
        return back()->with('success', 'Thank you for contact us!');
    }

    public function replyGuest(TicketThread $thread, string $hash, Request $request) {
        if ($hash !== $this->getReplyHash($thread)) {
            abort(403);
        }
        $request->validate(['message' => 'required|string', 'captcha' => 'required|captcha']);
        TicketMessage::create(['thread_id' => $thread->id, 'message' => e($request->message), 'guest' => $thread->message->guest, 'reply' => true]);
        return back()->with('success', 'Thank you for contact us!');
    }

    private function getReplyHash(TicketThread $thread): string {
        return md5($thread->id . '|' . $thread->message->guest);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////

    public function threadsIndex(Request $request) {
        return view('layouts.app', [
            'page_title' => 'Manage Tickets',
            'slot' =>
                view('components.list.list', [
                    'key' => 'open',
                    'header' => view('components.list.header', ['title' => 'Open Tickets', 'add' => $this->threadForm(), 'search' => $this->threadSearchForm('open')]),
                    'body' => $this->threadsList('open', $request)
                ])->render() .
                view('components.list.list', [
                    'key' => 'closed',
                    'header' => view('components.list.header', ['title' => 'Closed Tickets', 'search' => $this->threadSearchForm('closed')]),
                    'body' => $this->threadsList('closed', $request)
                ])->render()
        ]);
    }

    public function threadsList($key, Request $request) {
        if ($key !== 'open' && $key !== 'closed') {
            abort(422);
        }
        $user = Auth::user();
        $query = ($user->isManager() ? TicketThread::query() : $user->tickets()->getQuery())
            ->select(['tickets_threads.*'])
            ->closed($key === 'closed')
            ->with('message')
            ->withCount('messages')
            ->withMax('messages', 'updated_at');
        $threads = $this->searchThread($query)->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('tickets.threads.list', ['key' => $key], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Subject', 'Category', 'User', 'Last Updated', 'Actions'],
            'rows' => $threads->getCollection()->toString(fn($t) => $this->threadListRow($t)->render()),
            'pagination' => $threads->links()
        ]);
    }

    private function searchThread(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = \request();
            $this->whereString('tickets_threads.subject', $req->subject, $q);
            $this->whereEquals('tickets_threads.category', $req->category, $q);
            $this->whereGt('tickets_threads.created_at', $req->date_after, $q);
            $this->whereLt('tickets_threads.created_at', $req->date_before, $q);
            if ($req->hasAny('message', 'user', 'guest')) {
                $q->join('tickets_messages', function (JoinClause $qq) use ($req) {
                    $qq->whereColumn('tickets_messages.thread_id', '=', 'tickets_threads.id');
                    $this->whereString('tickets_messages.message', $req->message, $qq);

                    if (Auth::user()->isManager()) {
                        if ($req->has('guest')) {
                            $qq->whereNotNull("tickets_messages.guest");
                            $this->whereString('tickets_messages.guest', $req->guest, $qq);
                        }
                        if ($req->has('user')) {
                            $qq->whereNotNull("tickets_messages.user_id");
                            $qq->where('tickets_messages.user_id', '=', $req->user);
                        }
                    }
                });
            }
        }
        return $q;
    }

    protected function threadListRow(TicketThread $thread) {
        $badges = view('components.list.row-badge', ['class' => 'badge-info', 'value' => isset($thread->messages_count) ? $thread->messages_count - 1 : 0])->render();
        $close = '';
        if (self::canCloseThread($thread)) {
            $close = view('components.list.row-action', [
                'click' => 'Ads.item.updateRow(this)',
                'title' => $thread->closed ? 'Open' : 'Close',
                'icon' => $thread->closed ? 'mark_email_unread' : 'mark_email_read',
                'url' => route('tickets.threads.close', ['thread' => $thread->id, 'close' => intval(!$thread->closed)], false)
            ])->render();
        }
        return view('components.list.row', [
            'id' => $thread->id,
            'columns' => [
                Str::limit($thread->subject) . ' ' . $badges,
                $thread->category,
                $thread->message->guest ?? $thread->message->user->name,
                Carbon::parse($thread->messages_max_updated_at)->format('Y-m-d H:i')
            ],
            'show' => ['url' => route('tickets.threads.show', ['thread' => $thread->id], false)],
            'delete' => $this->canDestroyThread($thread) ? ['url' => route('tickets.threads.destroy', ['thread' => $thread->id], false)] : null,
            'extra' =>
                view('components.list.row-action', [
                    'click' => 'Ads.item.openExtra(this)',
                    'title' => "Replies",
                    'icon' => "reply_all",
                    'url' => route('tickets.messages.index', ['thread' => $thread->id], false)
                ])->render() . $close
        ]);
    }

    public function threadShow(TicketThread $thread) {
        if (!$this->canSeeThread($thread)) {
            abort(403);
        }

        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Ticket ID:', 'value' => $thread->id],
                ['caption' => 'Category:', 'value' => $thread->category],
				['caption' => 'Creation Date:', 'value' => $thread->message->created_at->format('Y-m-d H:i')],
                ['caption' => 'Subject:', 'value' => $thread->subject],
				['caption' => 'User:', 'value' => $thread->message->guest ?? $thread->message->user->name],
				['caption' => 'Closed:', 'value' => $thread->closed ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
                ['caption' => 'Message:', 'full' => true, 'value' => '<div style="font-size:15px"><br>' . nl2br($thread->message->message) . '</div>'],
            ]
        ]);
    }

    public function threadDestroy(TicketThread $thread) {
        if (!$this->canDestroyThread($thread)) {
            abort(403);
            return false;
        }
        return $thread->delete();
    }

    public function closeThread(TicketThread $thread, bool $close) {
        if (!$this->canCloseThread($thread)) {
            abort(403);
            return false;
        }
        if ($close && !$thread->closed) {
            $thread->closed = true;
        } elseif (!$close && $thread->closed) {
            $thread->closed = false;
        }
        $thread->save();
        return $this->threadListRow($thread);
    }

    public function threadStore(Request $request) {
        $validator = Validator::make($request->all(), [
            'category' => 'required|in:Publishers,Advertisers,Billing,Other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors()->toArray());
        }
        try {
            $thread = DB::transaction(function () use ($request) {
                $thread = TicketThread::create(['subject' => e($request->subject), 'category' => $request->category]);
                $thread->messages()->save(new TicketMessage(['message' => e($request->message), 'user_id' => Auth::id(), 'reply' => false]));
                return $thread;
            });
            return $this->success($this->threadListRow($thread)->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    private function threadForm() {
        return view('components.tickets.thread-form');
    }

    private function threadSearchForm($key) {
        $users_options = User::all()->toString(fn($user) => Helper::option($user->id, "$user->name"));
        return view('components.tickets.thread-search-form', compact('key', 'users_options'));
    }

    public function messagesIndex(TicketThread $thread, Request $request) {
        return view('components.list.list', [
            'key' => 'all',
            'header' => view('components.list.header', ['title' => 'Replies', 'add' => $this->messageForm($thread)]),
            'body' => $this->messagesList($thread, $request)
        ]);
    }

    public function messagesList(TicketThread $thread, Request $request) {
        if (!$this->canSeeThread($thread)) {
            abort(403);
        }
        $messages = $thread->messages()->reply()->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('tickets.messages.list', ['thread' => $thread->id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['User', 'Message', 'Time', 'Actions'],
            'rows' => $messages->getCollection()->toString(fn($m) => $this->messageListRow($m)->render()),
            'pagination' => $messages->links()
        ]);
    }

    protected function messageListRow(TicketMessage $message) {
        $data = [
            'id' => $message->id,
            'columns' => [$message->guest ?? $message->user->name, Str::limit($message->message), $message->created_at->format('Y-m-d H:i')],
            'show' => ['url' => route('tickets.messages.show', ['message' => $message->id], false)],
            'edit' => $this->canEditMessage($message) ? ['url' => route('tickets.messages.edit', ['message' => $message->id], false)] : null,
            'delete' => $this->canDestroyMessage($message) ? ['url' => route('tickets.messages.destroy', ['message' => $message->id], false)] : null
        ];
        if (!isset($data['edit']) || !isset($data['delete'])) {
            // Actions columns is empty and changes other columns positions.
            $data['extra'] = ''; // This makes empty actions and fixes positions
        }
        return view('components.list.row', $data);
    }

    public function messageShow(TicketMessage $message) {
        if (!$this->canSeeMessage($message)) {
            abort(403);
        }
        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Reply ID:', 'value' => $message->id],
                ['caption' => 'Creation Date:', 'value' => $message->created_at->format('Y-m-d H:i')],
                ['caption' => 'User:', 'value' => $message->guest ?? $message->user->name],
                ['caption' => 'Message:', 'full' => true, 'value' => '<div style="font-size:15px"><br>' . nl2br($message->message) . '</div>'],
            ]
        ]);
    }

    public function messageEdit(TicketMessage $message) {
        return $this->messageForm($message->thread, $message);
    }

    public function messageDestroy(TicketMessage $message) {
        if (!$this->canDestroyMessage($message)) {
            abort(403);
        }
        return $message->delete();
    }

    public function messageStore(TicketThread $thread, Request $request) {
        if (!$this->canReply($thread)) {
            abort(403);
        }
        $validator = Validator::make($request->all(), ['message' => 'required|string']);
        if ($validator->fails()) {
            return $this->failure($validator->errors()->toArray());
        }
        try {
            $message = DB::transaction(function () use ($request, $thread) {
                $message = TicketMessage::create(['thread_id' => $thread->id, 'message' => e($request->message), 'user_id' => Auth::id(), 'reply' => true]);
                if ($thread->isGuestThread()) {
                    $messages = [];
                    foreach ($thread->messages as $m) {
                        $messages[] = [
                            'time' => $m->created_at->format('Y-m-i H:i'),
                            'content' => $m->message,
                            'user' => $m->isGuestMessage() ? null : $m->user->name
                        ];
                    }
                    $replyUrl = route('contact.create-reply', ['thread' => $thread->id, 'hash' => $this->getReplyHash($thread)]);
                    Notification::route('mail', $thread->message->guest)->notify(new GuestTicketReply($thread->id, $messages, $replyUrl, closed: $thread->closed));
                }
                return $message;
            });
            return $this->success($this->messageListRow($message)->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function messageUpdate(TicketMessage $message, Request $request) {
        if (!$this->canEditMessage($message)) {
            abort(403);
        }
        $validator = Validator::make($request->all(), ['message' => 'required|string']);
        if ($validator->fails()) {
            return $this->failure($validator->errors()->toArray());
        }
        try {
            $message->update(['message' => e($request->message)]);
            return $this->success($this->messageListRow($message)->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    private function messageForm(TicketThread $thread, TicketMessage $message = null) {
        return view('components.tickets.reply-form', compact('thread', 'message'));
    }

    ///////////////////////////////////////////////////////

    private function canSeeThread(TicketThread $thread) {
        $user = Auth::user();
        return $user->isManager() || $thread->message->user_id === $user->id;
    }

    private function canDestroyThread(TicketThread $thread) {
        $user = Auth::user();
        return $user->isManager() || $thread->message->user_id === $user->id;
    }

    private function canCloseThread(TicketThread $thread) {
        return Auth::user()->isManager();
    }

    private function canSeeMessage(TicketMessage $message): bool {
        $user = Auth::user();
        return $user->isManager() || $message->user_id === $user->id || $message->thread->message->user_id === $user->id;
    }

    private function canReply(TicketThread $thread): bool {
        $user = Auth::user();
        return $user->isManager() || $thread->message->user_id === $user->id;
    }

    private function canEditMessage(TicketMessage $message): bool {
        $user = Auth::user();
        return !$message->thread->isGuestThread() && ($user->isManager() || $message->user_id === $user->id);
    }

    private function canDestroyMessage(TicketMessage $message): bool {
        $user = Auth::user();
        return $user->isAdmin() || $message->user_id === $user->id;
    }
}
