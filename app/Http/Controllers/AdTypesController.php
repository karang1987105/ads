<?php

namespace App\Http\Controllers;

use App\Models\AdBanner;
use App\Models\AdType;
use App\Models\AdVideo;
use App\Models\InvoiceCampaign;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Storage;
use Validator;

class AdTypesController extends Controller {
    public function index(Request $request) {
        return view('layouts.app', [
            'page_title' => 'Manage AD Types',
            'slot' => view('components.list.list', ['key' => 'all', 'header' => $this->listHeader(), 'body' => $this->list($request)])
        ]);
    }

    public function activate(AdType $adType, $active) {
        if ($active && !$adType->active) {
            $adType->active = true;
        } elseif (!$active && $adType->active) {
            $adType->active = false;
        }
        $adType->save();
        return $this->listRow($adType);
    }

    public function list(Request $request) {
        $adTypes = $this->search(AdType::query())->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('admin.ad-types.list', absolute: false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Name', 'Kind', 'Type', 'Size', 'Target Device', 'Active', 'Actions'],
            'rows' => $adTypes->getCollection()->toString(fn($adType) => $this->listRow($adType)->render()),
            'pagination' => $adTypes->links()
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = request();
            $this->whereString('ads_types.name', $req->name, $q);
            $this->whereEquals('ads_types.type', $req->type, $q);
            $this->whereBoolean('ads_types.active', $req->active, $q);
        }
        return $q;
    }

    private function listRow(AdType $adType) {
        return view('components.list.row', [
            'id' => $adType->id,
            'columns' => [
                $adType->name,
                strtoupper($adType->kind),
                $adType->type,
                $adType->getSize(),
                $adType->device,
                $adType->active ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
            ],
            'show' => ['url' => route('admin.ad-types.show', ['ad_type' => $adType->id], false)],
            'edit' => ['url' => route('admin.ad-types.edit', ['ad_type' => $adType->id], false)],
            'delete' => ['url' => route('admin.ad-types.destroy', ['ad_type' => $adType->id], false)],
            'extra' => view('components.list.row-action', [
                'click' => 'Ads.item.updateRow(this)',
                'title' => $adType->active ? 'Disable' : 'Enable',
                'icon' => $adType->active ? 'block' : 'task_alt',
                'url' => route('admin.ad-types.activate', ['ad_type' => $adType->id, 'active' => intval(!$adType->active)], false)
            ])
        ]);
    }

    public function show(AdType $adType) {
        $items = [
            ['caption' => 'AD Type ID:', 'value' => $adType->id],
            ['caption' => 'Type:', 'value' => $adType->type],
            ['caption' => 'Kind:', 'value' => $adType->kind ?? ''],
            ['caption' => 'Target Device:', 'value' => $adType->device],
            ['caption' => 'Size:', 'value' => $adType->getSize()],
            ['caption' => 'Active:', 'value' => $adType->active ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
        ];
        return view('components.list.row-details', ['rows' => $items]);
    }

    private function form($adType = null) {
        return view('components.adtypes.form', compact('adType'));
    }

    private function searchForm() {
        return view('components.adtypes.search-form');
    }

    private function listHeader() {
        return view('components.list.header', ['title' => 'Available AD Types', 'add' => $this->form(), 'search' => $this->searchForm()]);
    }

    public function edit(AdType $adType) {
        return $this->form($adType);
    }

    public function destroy(AdType $adType) {
        return DB::transaction(function () use ($adType) {
            // Move back balances to user
            InvoiceCampaign::query()
                ->join('campaigns', function ($q) {
                    $q->whereColumn('campaigns.id', 'invoices_campaigns.campaign_id')
                        ->whereNull('campaigns.deleted_at');
                })
                ->join('ads', function ($q) use ($adType) {
                    $q->whereColumn('ads.id', 'campaigns.ad_id')
                        ->where('ads.ad_type_id', $adType->id)
                        ->whereNull('ads.deleted_at');
                })
                ->update(['invoices_campaigns.amount' => DB::raw('invoices_campaigns.current')]);

            // Delete banner files
            AdBanner::select('ads_banners.file')
                ->join('ads', fn($q) => $q->whereColumn('ads.id', 'ads_banners.ad_id')->where('ad_type_id', $adType->id))
                ->pluck('file')
                ->each(fn($path) => Storage::delete('public/' . $path));

            // Delete video files
            AdVideo::select('ads_videos.file')
                ->join('ads', fn($q) => $q->whereColumn('ads.id', 'ads_videos.ad_id')->where('ad_type_id', $adType->id))
                ->pluck('file')
                ->each(fn($path) => Storage::delete('public/' . $path));

            return $adType->delete();
        });
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'type' => 'required|in:Banner,Video',
            'width' => 'required|integer',
            'height' => 'required|integer',
            'kind' => 'exclude_unless:type,Banner|required|in:CPC,CPM',
            'device' => 'required|in:All,Mobile,Desktop'
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        try {
            $adType = AdType::create([
                'name' => $request->name,
                'type' => $request->type,
                'kind' => $request->type === 'Video' ? 'CPV' : $request->kind,
                'device' => $request->device,
                'width' => $request->width,
                'height' => $request->height,
                'active' => isset($request->active) ? 1 : 0,
            ]);
            return $this->success($this->listRow($adType)->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Request $request, AdType $adType) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'width' => 'required|integer',
            'height' => 'required|integer',
            'kind' => 'exclude_unless:type,Banner|required|in:CPC,CPM',
            'device' => 'required|in:All,Mobile,Desktop'
        ]);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        $request['active'] = $request->boolean('active') ? 1 : 0;
        if ($adType->isBanner() && $request->type === 'Video') {
            $request['kind'] = 'CPV';
        }

        try {
            $adType->update($request->all());
            return $this->success($this->listRow($adType)->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }
}
