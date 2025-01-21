<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Domains\AdvertiserDomain;
use App\Models\User;
use App\Models\UserAdvertiser;
use App\Notifications\UserUpdate;
use App\Rules\Domain;
use App\Rules\UniqueDomain;
use Auth;
use DB;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Admin and Manager actions
 */
class AdvertisersDomainsController extends Controller {

    public function __construct() {
        $this->middleware(function ($request, $next) {
            /** @var User $user */
            $user = auth()->user();
            if (!$user->isAdmin() && !User::hasPermission('advertisers', 'Domains')) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(UserAdvertiser $advertiser, Request $request) {
        return view('components.list.list', [
            'key' => 'all',
            'header' => $this->listHeader($advertiser),
            'body' => $this->list($advertiser, $request)
        ]);
    }

    public function list(UserAdvertiser $advertiser, Request $request) {
        $domains = $advertiser->domains()
            ->select(['users_advertisers_domains.*', DB::raw('active_ads.cnt AS active_ads')])
            ->leftJoinSub(AdvertiserDomain::activeAdsJoin($advertiser->user_id), 'active_ads', 'active_ads.id', '=', 'users_advertisers_domains.id')
            ->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('admin.advertisers.domains.list', ['advertiser' => $advertiser->user_id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Domain', 'Approved', 'Actions'],
            'rows' => $domains->getCollection()->toString(fn($domain) => $this->listRow($domain)->render()),
            'pagination' => $domains->links()
        ]);
    }

    protected function listRow(AdvertiserDomain $domain) {
        $isApproved = $domain->isApproved();
        $edit = $delete = null;
        $extra = [];
        $inUse = isset($domain->active_ads) ? $domain->active_ads > 0 : AdvertiserDomain::inUse($domain);
		$extra[] = view('components.list.row-action', [
            'click' => 'Ads.item.updateRow(this)',
            'title' => $isApproved ? 'Decline' : 'Approve',
            'icon' => $isApproved ? 'block' : 'task_alt',
            'url' => route('admin.advertisers.domains.approve', ['domain' => $domain->id, 'approve' => intval(!$isApproved)], false)
        ])->render();
        if (!$inUse) {
            $edit = ['url' => route('admin.advertisers.domains.edit', ['domain' => $domain->id], false)];
            $delete = ['url' => route('admin.advertisers.domains.destroy', ['domain' => $domain->id], false)];
        } else {
            $extra[] = view('components.list.row-action', ['class' => 'disabled', 'title' => 'This domain is already in use', 'icon' => 'delete_forever'])->render();
            $extra[] = view('components.list.row-action', ['class' => 'disabled', 'title' => 'This domain is already in use', 'icon' => 'border_color'])->render();
        }
        return view('components.list.row', [
            'id' => $domain->id,
            'columns' => [
                $domain->domain,
                $domain->approved_at ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
						 ],
            'show' => ['url' => route('admin.advertisers.domains.show', ['domain' => $domain->id], false)],
            'edit' => $edit,
            'delete' => $delete,
            'extra' => $extra
        ]);
    }

    private function listHeader(UserAdvertiser $advertiser) {
        return view('components.list.header', ['title' => 'Existing Domains', 'add' => $this->form($advertiser)]);
    }

    private function form(UserAdvertiser $advertiser, AdvertiserDomain $domain = null) {
        $params = ['advertiser' => $advertiser->user_id];
        if ($domain != null) {
            $params['domain'] = $domain;
        }
        return view('components.domains.advertiser-form', $params);
    }

    public function show(AdvertiserDomain $domain) {
        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Domain ID:', 'value' => $domain->id],
                ['caption' => 'Domain:', 'value' => $domain->domain],
                ['caption' => 'Approved:', 'full' => true, 'value' => $domain->isApproved() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],				
                ['caption' => 'Approved By:', 'full' => true, 'value' => $domain->isApproved() ? "{$domain->approvedBy->user->name} at " . $domain->approved_at->format('Y-m-d H:i') : 'Nobody']
            ]
        ]);
    }

    public function edit(AdvertiserDomain $domain) {
        return $this->form($domain->advertiser, $domain);
    }

    public function destroy(AdvertiserDomain $domain) {
        if (AdvertiserDomain::inUse($domain)) {
            abort(422);
        }
        return $domain->delete();
    }

    public function store(UserAdvertiser $advertiser, Request $request) {
        $request['domain'] = Domain::sanitizeDomain($request->domain);

        $validator = Validator::make($request->all(), [
            'domain' => [
                'required',
                new Domain,
                new UniqueDomain('users_advertisers_domains', 'domain')
            ]
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        try {
            $domain = AdvertiserDomain::create([
                'advertiser_id' => $advertiser->user_id,
                'domain' => $request->domain,
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

    public function update(AdvertiserDomain $domain, Request $request) {
        if (AdvertiserDomain::inUse($domain)) {
            abort(422);
        }
        $request['domain'] = Domain::sanitizeDomain($request->domain);

        $validator = Validator::make($request->all(), [
            'domain' => [
                'required',
                new Domain,
                new UniqueDomain('users_advertisers_domains', 'domain', $domain->id)
            ]
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
            $domain->update($request->all());

            if ($statusChanged) {
                $domain->advertiser->user->notifyUser(
                    $domain->isApproved() ? UserUpdate::$TYPE_DOMAIN_APPROVED : UserUpdate::$TYPE_DOMAIN_DECLINED,
                    ['domain' => $domain->domain]
                );
            }

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

    public function approve(AdvertiserDomain $domain, $approve) {
        if ($approve && !$domain->isApproved()) {
            $domain->approved_by_id = Auth::id();
            $domain->approved_at = now();
        } elseif (!$approve && $domain->isApproved()) {
            $domain->approved_by_id = null;
            $domain->approved_at = null;
        }
        $domain->save();

        $domain->advertiser->user->notifyUser(
            $domain->isApproved() ? UserUpdate::$TYPE_DOMAIN_APPROVED : UserUpdate::$TYPE_DOMAIN_DECLINED,
            ['domain' => $domain->domain]
        );

        return $this->listRow($domain);
    }
}
