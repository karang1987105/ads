<?php

namespace App\Http\Controllers\Ads;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdType;
use Auth;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdsLimitedController extends Controller {
    use AdsTrait;

    public function index(Request $request)
    {

        $headerOptions = [
            'title' => 'Available ADS'
        ];

        $domainsExists = Auth::user()->advertiser->domains()
            ->whereNotNull('approved_at')
            ->exists();

        if ($domainsExists) {
            $headerOptions['add'] = '<form data-name="add"></form>';
            $headerOptions['add_url'] = route('advertiser.ads.create', absolute: false);
        } else {
            $headerOptions['actions'] = [
                [
                    'title' => 'There is no active domain available!',
                    'disabled' => true,
                    'icon' => 'add_box',
                    'click' => '',
                ]
            ];
        }

        return view('layouts.app', [
            'page_title' => 'Manage ADS',
            'slot' => view('components.list.list', [
                'key' => 'all',
                'header' => view('components.list.header', $headerOptions),
                'body' => $this->list($request)
            ])
        ]);
    }

    public function list(Request $request) {
        $ads = $this->search(Auth::user()->advertiser->ads()->getQuery())->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('advertiser.ads.list', absolute: false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Title', 'AD Type', 'AD Size', 'Domain', 'Approved', 'Actions'],
            'rows' => $ads->getCollection()->toString(fn($ad) => $this->listRow($ad)->render()),
            'pagination' => $ads->links()
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = \request();

            $this->whereEquals('ads.ad_type_id', $req->ad_type, $q);

            if ($req->has('ad_type_name')) {
                $q->join('ads_types', function ($qq) use ($req) {
                    $qq->whereColumn('ads_types.id', 'ads.ad_type_id');
                    $this->whereString('ads_types.name', $req->ad_type_name, $qq);
                });
            }

            if ($req->has('title')) {
                $q->leftJoin('ads_banners', 'ads_banners.ad_id', '=', 'ads.id');
                $q->leftJoin('ads_videos', 'ads_videos.ad_id', '=', 'ads.id');
                $q->whereNested(function ($qq) use ($req) {
                    $qq->where('ads_banners.title', '=', $req->title);
                    $qq->orWhere('ads_videos.title', '=', $req->title);
                });
            }
        }
        return $q;
    }

    protected function listRow(Ad $ad) {
        $isApproved = $ad->isApproved();
        return view('components.list.row', [
            'id' => $ad->id,
            'columns' => [
			    $ad->isBanner() ? $ad->banner->title : $ad->video->title,
                $ad->adType->name,
				$ad->adType->getSize(),
                $ad->isBanner() ? $ad->banner->domain->domain : $ad->video->domain->domain,
                $isApproved ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
            ],
            'show' => ['url' => route('advertiser.ads.show', ['ad' => $ad->id], false)],
            'edit' => ['url' => route('advertiser.ads.edit', ['ad' => $ad->id], false)],
            'delete' => ['url' => route('advertiser.ads.destroy', ['ad' => $ad->id], false)],
            'extra' => view('components.list.row-action', [
                'click' => 'Ads.item.openExtra(this)',
                'title' => "Campaigns",
                'icon' => "campaign",
                'url' => route('advertiser.ads.campaigns.index', ['ad' => $ad->id], false)
            ])
        ]);
    }

    public function show(Ad $ad) {
        return $this->_show($ad);
    }

    private function form(Ad $ad = null) {
        return view('components.ads.limited-form', $this->_formData(Auth::user()->advertiser, $ad));
    }

    private function searchForm() {
        $adtype_options = AdType::active()->get()
            ->toString(fn($adType) => Helper::option($adType->id, "$adType->name " . $adType->getSize()));
        return view('components.ads.limited-search-form', compact('adtype_options'));
    }

    public function edit(Ad $ad) {
        return $this->form($ad);
    }

    public function create() {
        return $this->form();
    }

    public function destroy(Ad $ad) {
        try {
            return $this->_destroy($ad);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function store(Request $request) {
        try {
            return $this->_store($request, Auth::user()->advertiser);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Ad $ad, Request $request) {
        try {
            return $this->_update($ad, $request);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }
}
