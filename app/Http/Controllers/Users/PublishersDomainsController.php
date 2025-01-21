<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Domains\PublisherDomain;
use App\Models\User;
use App\Models\UserPublisher;
use App\Notifications\UserUpdate;
use App\Rules\Domain;
use App\Rules\UniqueDomain;
use Auth;
use DB;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Admin and Manager actions
 */
class PublishersDomainsController extends Controller {

    public function __construct() {
        $this->middleware(function ($request, $next) {
            /** @var User $user */
            $user = auth()->user();
            if (!$user->isAdmin() && !User::hasPermission('publishers', 'Domains')) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(UserPublisher $publisher, Request $request) {
        return view('components.list.list', [
            'key' => 'all',
            'header' => $this->listHeader($publisher),
            'body' => $this->list($publisher, $request)
        ]);
    }

    public function list(UserPublisher $publisher, Request $request) {
        $domains = $publisher->domains()
            ->addSelect(['active_places' => PublisherDomain::activePlacesCount()])
            ->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('admin.publishers.domains.list', ['publisher' => $publisher->user_id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Domain', 'Category', 'Approved', 'Actions'],
            'rows' => $domains->getCollection()->toString(fn($domain) => $this->listRow($domain)->render()),
            'pagination' => $domains->links()
        ]);
    }

    protected function listRow(PublisherDomain $domain) {
        $isApproved = $domain->isApproved();
        $edit = $delete = null;
        $extra = [];
        $inUse = isset($domain->active_places) ? $domain->active_places > 0 : PublisherDomain::inUse($domain);
		$extra[] = view('components.list.row-action', [
            'click' => 'Ads.item.updateRow(this)',
            'title' => $isApproved ? 'Decline' : 'Approve',
            'icon' => $isApproved ? 'block' : 'task_alt',
            'url' => route('admin.publishers.domains.approve', ['domain' => $domain->id, 'approve' => intval(!$isApproved)], false)
        ])->render();
        if (!$inUse) {
            $edit = ['url' => route('admin.publishers.domains.edit', ['domain' => $domain->id], false)];
            $delete = ['url' => route('admin.publishers.domains.destroy', ['domain' => $domain->id], false)];
        } else {
            $extra[] = view('components.list.row-action', ['class' => 'disabled', 'title' => 'This domain is already in use', 'icon' => 'delete_forever'])->render();
            $extra[] = view('components.list.row-action', ['class' => 'disabled', 'title' => 'This domain is already in use', 'icon' => 'border_color'])->render();
        }
        return view('components.list.row', [
            'id' => $domain->id,
            'columns' => [
                $domain->domain,
                $domain->category?->title,
                $domain->isApproved() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
            ],
            'show' => ['url' => route('admin.publishers.domains.show', ['domain' => $domain->id], false)],
            'edit' => $edit,
            'delete' => $delete,
            'extra' => $extra
        ]);
    }

    private function listHeader(UserPublisher $publisher) {
        return view('components.list.header', ['title' => 'Existing Domains', 'add' => $this->form($publisher)]);
    }

    private function form(UserPublisher $publisher, PublisherDomain $domain = null) {
        $category_options = Category::active()->get()->toString(function ($category) use ($domain) {
            return Helper::option(
                $category->id,
                $category->title,
                $domain?->category_id == $category->id,
                [
                    'data-subtext' => "CPM: " . round($category->cpm, 5) . ", CPC: " . round($category->cpc, 5) .
                        ", CPV: " . round($category->cpv, 5) . ", Revenue: " . round($category->revenue_share, 2) . "%"
                ]
            );
        });
        $params = ['publisher' => $publisher->user_id, 'category_options' => $category_options];
        if ($domain != null) {
            $params['domain'] = $domain;
        }
        return view('components.domains.publisher-form', $params);
    }

    public function show(PublisherDomain $domain) {
        $domain->load('category');
        $category = $domain->category;
        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Domain ID:', 'value' => $domain->id],
                ['caption' => 'Domain:', 'value' => $domain->domain],
                ['caption' => 'Category:', 'value' => $category->title],				
                ['caption' => 'Approved:', 'value' => $domain->isApproved() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
				['caption' => 'Approved By:', 'full' => true, 'value' => $domain->isApproved() ? "{$domain->approvedBy->user->name} at " . $domain->approved_at->format('Y-m-d H:i') : 'Nobody']
            ]
        ]);
    }

    public function edit(PublisherDomain $domain) {
        return $this->form($domain->publisher, $domain);
    }

    public function destroy(PublisherDomain $domain) {
        if (PublisherDomain::inUse($domain)) {
            abort(422);
        }
        return $domain->delete();
    }

    public function store(UserPublisher $publisher, Request $request) {
        $request['domain'] = Domain::sanitizeDomain($request->domain);

        $validator = Validator::make($request->all(), [
            'domain' => [
                'required',
                new Domain,
                new UniqueDomain('users_publishers_domains', 'domain')
            ],
            'category_id' => 'exists:App\Models\Category,id'
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        try {
            $domain = PublisherDomain::create([
                'publisher_id' => $publisher->user_id,
                'domain' => $request->domain,
                'category_id' => $request->category_id,
                'approved_by_id' => isset($request->approve) ? Auth::id() : null,
                'approved_at' => isset($request->approve) ? now() : null,
            ]);
            return $this->success($this->listRow($domain)->render());
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return $this->failure(['domain' => 'This domain exists already!']);
            }
            return $this->exception($e);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(PublisherDomain $domain, Request $request) {
        if (PublisherDomain::inUse($domain)) {
            abort(422);
        }
        $request['domain'] = Domain::sanitizeDomain($request->domain);

        $validator = Validator::make($request->all(), [
            'domain' => [
                'required',
                new Domain,
                new UniqueDomain('users_publishers_domains', 'domain', $domain->id)
            ],
            'category_id' => 'exists:App\Models\Category,id'
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        if ($request->approve && !$domain->isApproved()) {
            $request['approved_by_id'] = Auth::id();
            $request['approved_at'] = now();
        } elseif (!$request['approve'] && $domain->isApproved()) {
            $request['approved_by_id'] = null;
            $request['approved_at'] = null;
        }

        $statusChanged = $request->approve != $domain->isApproved();

        try {
            DB::transaction(function () use ($statusChanged, $request, $domain) {
                $domain->update($request->all());
                if (!$domain->isApproved()) {
                    $this->domainUnapproved($domain);
                }

                if ($statusChanged) {
                    $domain->publisher->user->notifyUser(
                        $domain->isApproved() ? UserUpdate::$TYPE_DOMAIN_APPROVED : UserUpdate::$TYPE_DOMAIN_DECLINED,
                        ['domain' => $domain->domain]
                    );
                }
            });

            return $this->success($this->listRow($domain->fresh())->render());

        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return $this->failure(['domain' => 'This domain exists already!']);
            }
            return $this->exception($e);

        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function approve(PublisherDomain $domain, $approve) {
        if ($approve && !$domain->isApproved()) {
            $domain->approved_by_id = Auth::id();
            $domain->approved_at = now();
        } elseif (!$approve && $domain->isApproved()) {
            $domain->approved_by_id = null;
            $domain->approved_at = null;
        }

        DB::transaction(function () use ($domain) {
            $domain->save();
            if (!$domain->isApproved()) {
                $this->domainUnapproved($domain);
            }

            $domain->publisher->user->notifyUser(
                $domain->isApproved() ? UserUpdate::$TYPE_DOMAIN_APPROVED : UserUpdate::$TYPE_DOMAIN_DECLINED,
                ['domain' => $domain->domain]
            );
        });

        return $this->listRow($domain);
    }

    private function domainUnapproved(PublisherDomain $domain) {
        $domain->places()->update(['approved_at' => null]);
    }
}
