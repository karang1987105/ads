<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Domains\AdvertiserDomain;
use App\Models\User;
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
class AdminsDomainsController extends Controller {

    public function index() {
        return view('components.list.list', [
            'key' => 'admin-domains',
            'header' => $this->listHeader(),
            'body' => $this->list()
        ])->render();
    }

    public function list() {
        /** @var User $admin */
        $admin = auth()->user();
        $request = \request();

        $domains = AdvertiserDomain::query()->whereNull('advertiser_id')
            ->select(['users_advertisers_domains.*', DB::raw('active_ads.cnt AS active_ads')])
            ->leftJoinSub(AdvertiserDomain::activeAdsJoin($admin->id), 'active_ads', 'active_ads.id', '=', 'users_advertisers_domains.id')
            ->page($request->query->get('page'));

        return view('components.list.body', [
            'url' => route('admin.admin-domains.list', absolute: false),
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
            'url' => route('admin.admin-domains.approve', ['domain' => $domain->id, 'approve' => intval(!$isApproved)], false)
        ])->render();
        if (!$inUse) {
            $edit = ['url' => route('admin.admin-domains.edit', ['domain' => $domain->id], false)];
            $delete = ['url' => route('admin.admin-domains.destroy', ['domain' => $domain->id], false)];
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
            'show' => ['url' => route('admin.admin-domains.show', ['domain' => $domain->id], false)],
            'edit' => $edit,
            'delete' => $delete,
            'extra' => $extra
        ]);
    }

    private function listHeader() {
        return view('components.list.header', ['title' => 'System Domains', 'add' => $this->form()]);
    }

    private function form(AdvertiserDomain $domain = null) {
        return view('components.domains.admin-form', compact('domain'));
    }

    public function show(AdvertiserDomain $domain) {
        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Domain ID:', 'value' => $domain->id],
                ['caption' => 'Domain:', 'value' => $domain->domain],
                ['caption' => 'Approved:', 'value' => $domain->isApproved() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
            ]
        ]);
    }

    public function edit(AdvertiserDomain $domain) {
        return $this->form($domain);
    }

    public function destroy(AdvertiserDomain $domain) {
        if (AdvertiserDomain::inUse($domain)) {
            abort(422);
        }
        return $domain->delete();
    }

    public function store(Request $request) {
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

        $adminId = auth()->id();
        try {
            $domain = AdvertiserDomain::create([
                'domain' => $request->domain,
                'approved_by_id' => isset($request->approve) ? $adminId : null,
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
            $request['approved_by_id'] = auth()->id();
            $request['approved_at'] = now();

        } elseif (!$request['approve'] && $domain->isApproved()) {
            $request['approved_by_id'] = null;
            $request['approved_at'] = null;

        }

        try {
            $domain->update($request->all());

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

        return $this->listRow($domain);
    }
}
