<?php

namespace App\Http\Controllers\Ads;

use App\Helpers\Helper;
use App\Models\Ad;
use App\Models\AdBanner;
use App\Models\AdThirdParty;
use App\Models\AdType;
use App\Models\AdVideo;
use App\Models\Campaign;
use App\Models\Domains\AdvertiserDomain;
use App\Models\UserAdvertiser;
use App\Notifications\UserUpdate;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Storage;
use Str;

trait AdsTrait {
    protected function _formData(?UserAdvertiser $advertiser, Ad $ad = null) {
        if (!isset($ad)) {
            $adtype_options = AdType::active()->get()->toString(function ($adType) {
                return Helper::option(
                    $adType->id,
                    $adType->name . '  ' . $adType->getSize(),
                    false,
                    ['data-type' => $adType->type]
                );
            });
        } else {
            $adType = $ad->adType;
            $adtype_options = Helper::option(
                $adType->id,
                $adType->name . ' ' . $adType->getSize(),
                true,
                ['data-type' => $adType->type]
            );
        }

        $adDomain = $ad?->getDomain(true);
        $domain_options = AdvertiserDomain::whereAdvertiserId($advertiser?->user_id)
            ->whereNotNull('approved_at')->get()
            ->toString(fn($domain) => Helper::option($domain->id, $domain->domain, $domain->id === $adDomain));

        return compact('ad', 'adtype_options', 'domain_options') + ['advertiser' => $advertiser?->user_id];
    }

    protected function _show(Ad $ad) {
        $rows = [
            ['caption' => 'AD ID:', 'value' => $ad->id],
			['caption' => 'Approved:', 'value' => $ad->isApproved() ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
			['caption' => 'Approved By:', 'full' => true, 'value' => $ad->isApproved() ? "{$ad->approvedBy->user->name} at " . $ad->approved_at->format('Y-m-d h:i') : 'Nobody'],		
			];
			
        if ($ad->isThirdParty()) {
            $rows[] =
			['caption' => 'AD Code:', 'full' => true, 'value' => '<div style="font-size:12px">' . htmlspecialchars($ad->thirdParty->code) . '</div>'
			];
        } else {
            $domain = $ad->getDomain();
            $rows[] = [
                'caption' => 'URL:',
                'full' => false,
                'value' => ($domain->category != null ? '[' . $domain->category->title . '] ' : '')
                    . $domain->domain . $ad->getUrl() . ' ' . Helper::link($ad->getUrl(true))
            ];

            if ($ad->isBanner()) {
                $rows[] = ['caption' => 'Banner Preview:', 'full' => true, 'value' => '<div>' . Helper::bannerImg($ad->banner, $ad->adType->width, $ad->adType->height) . '</div>'];
            } else {
                $rows[] = ['caption' => 'Video Preview:', 'full' => true, 'value' => '<div>' . Helper::videoPlayer($ad->video, $ad->adType->width, $ad->adType->height) . '</div>'];
            }
        }
        return view('components.list.row-details', ['rows' => $rows]);
    }

    protected function _store(Request $request, ?UserAdvertiser $advertiser = null) {
        $adType = null;
        if ($request->ad_type_id) {
            $adType = AdType::active()->find($request->ad_type_id);
        }
        if ($adType == null) {
            return $this->failure(['ad_type_id' => 'Ad Type value is invalid.']);
        }
        $isThirdParty = $request->boolean('third_party');
        if (!$this->isAdmin() && $isThirdParty) {
            abort(403);
        }

        if ($isThirdParty) {
            $rules = [
                'thirdparty.title' => 'required|string',
                'thirdparty.code' => 'required|string',
            ];
        } elseif ($adType->isBanner()) {
            $rules = [
                'banner.title' => 'required|string',
                'banner.file' => 'required|mimes:' . implode(',', config('ads.ads.banners.extensions')) . '|max:' . config('ads.ads.banners.max_size'),
                'banner.url' => 'required',
                'domain_id' => 'required|exists:App\Models\Domains\AdvertiserDomain,id',
            ];
        } else {
            $rules = [
                'video.title' => 'required|string',
                'video.file' => 'required|mimes:' . implode(',', config('ads.ads.videos.extensions')) . '|max:' . config('ads.ads.videos.max_size'),
                'video.url' => 'required',
                'domain_id' => 'required|exists:App\Models\Domains\AdvertiserDomain,id',
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        if (!$isThirdParty) {
            $domainExists = AdvertiserDomain::whereAdvertiserId($advertiser?->user_id)
                ->approved()
                ->where('id', $request->domain_id)
                ->exists();

            if (!$domainExists) {
                return $this->failure(['domain_id' => 'Domain value is invalid.']);
            }
        }

        $ad = DB::transaction(function () use ($isThirdParty, $request, $adType, $advertiser) {
            $ad = Ad::create([
                'ad_type_id' => $request->ad_type_id,
                'advertiser_id' => !$isThirdParty ? $advertiser?->user_id : null,
                'approved_by_id' => $request->boolean('approve') ? Auth::id() : null,
                'approved_at' => $request->boolean('approve') ? now() : null,
                'is_third_party' => (int)$isThirdParty
            ]);

            if ($isThirdParty) {
                $ad->thirdParty()->save(
                    new AdThirdParty([
                        'title' => $request->input('thirdparty.title'),
                        'code' => $request->input('thirdparty.code')
                    ]));
            } elseif ($adType->isBanner()) {
                $path = $this->tryUploadFile($request->file('banner.file'), $ad);
                if ($path === false) {
                    return $this->failure(['banner.file' => 'Upload failed.']);
                }

                $ad->banner()->save(
                    new AdBanner([
                        'title' => $request->input('banner.title'),
                        'file' => $path,
                        'url' => e($request->input('banner.url')),
                        'domain_id' => $request->input('domain_id'),
                    ])
                );
            } else {
                $path = $this->tryUploadFile($request->file('video.file'), $ad);
                if ($path === false) {
                    return $this->failure(['video.file' => 'Upload failed.']);
                }

                $ad->video()->save(
                    new AdVideo([
                        'title' => $request->input('video.title'),
                        'file' => $path,
                        'url' => e($request->input('video.url')),
                        'domain_id' => $request->input('domain_id'),
                    ])
                );
            }

            return $ad;
        });

        $ad->refresh();

        return $this->success(($ad->isThirdParty() ? $this->thirdPartyListRow($ad) : $this->listRow($ad))->render());
    }

    protected function _update(Ad $ad, Request $request) {
        if ($ad->isThirdParty()) {
            $rules = [
                'thirdparty.title' => 'required|string',
                'thirdparty.code' => 'required|string',
            ];
        } elseif ($ad->isBanner()) {
            $rules = [
                'banner.title' => 'required|string',
                'domain_id' => 'required|exists:App\Models\Domains\AdvertiserDomain,id',
            ];
            if ($request->hasFile('banner.file')) {
                $rules['banner.file'] = 'required|mimes:' . join(',', config('ads.ads.banners.extensions')) . '|max:' . config('ads.ads.banners.max_size');
            }
        } elseif ($ad->isVideo()) {
            $rules = [
                'video.title' => 'required|string',
                'domain_id' => 'required|exists:App\Models\Domains\AdvertiserDomain,id',
            ];
            if ($request->hasFile('video.file')) {
                $rules['video.file'] = 'required|mimes:' . join(',', config('ads.ads.videos.extensions')) . '|max:' . config('ads.ads.videos.max_size');
            }
        }

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }
        if ($ad->isNormalAd() && ($ad->isBanner() || $ad->isVideo())) {
            if ($ad->advertiser->domains()->approved()->find($request->domain_id) === null) {
                return $this->failure(['domain_id' => 'Ad Type value is invalid.']);
            }
        }

        DB::transaction(function () use ($ad, $request) {
            if ($request->approve && !$ad->isApproved()) {
                $this->_approveModel($ad, true);
            } elseif (!$request['approve'] && $ad->isApproved()) {
                $this->_approveModel($ad, false);
            }

            if ($ad->isThirdParty()) {
                $ad->update(['ad_type_id' => $request->ad_type_id]);
                $ad->thirdParty->update([
                    'title' => $request->input('thirdparty.title'),
                    'code' => $request->input('thirdparty.code'),
                ]);

            } elseif ($ad->isBanner()) {
                $attributes = [
                    'title' => $request->input('banner.title'),
                    'url' => $request->input('banner.url'),
//                    'alt_text' => $request->input('banner.alt_text'),
                    'domain_id' => $request->input('domain_id'),
                ];
                if ($request->hasFile('banner.file')) {
                    $fullPath = $this->tryChangeFile($request->file('banner.file'), $ad);
                    if ($fullPath === false) {
                        return $this->failure(['banner.file' => 'Upload failed.']);
                    }
                    $attributes['file'] = $fullPath;
                }
                $ad->banner->update($attributes);

            } elseif ($ad->isVideo()) {
                $attributes = [
                    'title' => $request->input('video.title'),
                    'url' => $request->input('video.url'),
//                    'alt_text' => $request->input('video.alt_text'),
                    'domain_id' => $request->input('domain_id'),
//                    'loop' => $request->boolean('video.loop')
                ];
                if ($request->hasFile('video.file')) {
                    $fullPath = $this->tryChangeFile($request->file('video.file'), $ad);
                    if ($fullPath === false) {
                        return $this->failure(['video.file' => 'Upload failed.']);
                    }
                    $attributes['file'] = $fullPath;
                }
                $ad->video->update($attributes);
            }

            return true;
        });
        $ad->refresh();
        return $this->success(($ad->isThirdParty() ? $this->thirdPartyListRow($ad) : $this->listRow($ad))->render());
    }

    /**
     * @throws Exception
     */
    protected function _destroy(Ad $ad): mixed {
        return DB::transaction(function () use ($ad) {

            // Balance campaigns
            $ad->campaigns->each(function (Campaign $c) {
                $c->invoices()->update(['amount' => DB::raw('`current`')]);
                $c->delete();
            });

            if ($ad->delete()) {
                if ($ad->hasFile()) {
                    Storage::delete('public/' . $ad->getFilePath());
                }

                return true;
            }

            throw new Exception("Deleting failed");
        });
    }

    protected function _approveModel(Ad $ad, bool $approve) {
        $statusChanged = $approve != $ad->isApproved();

        if ($approve && !$ad->isApproved()) {
            $ad->approved_by_id = \Auth::id();
            $ad->approved_at = now();
        } elseif (!$approve && $ad->isApproved()) {
            $ad->approved_by_id = null;
            $ad->approved_at = null;
        }

        $ad->save();

        if ($statusChanged && $ad->isNormalAd()) {
            $ad->advertiser->user->refresh()->notifyUser(
                $ad->isApproved() ? UserUpdate::$TYPE_AD_APPROVED : UserUpdate::$TYPE_AD_DECLINED,
                ['ad' => $ad]
            );
        }

        return $ad;
    }

    /**
     * @param UploadedFile $file
     * @param Ad $ad
     * @return bool|string uploaded path on success, and false on failure
     */
    private function tryUploadFile(UploadedFile $file, Ad $ad): bool|string {
        [$path, $name] = $this->getUploadedFilePath($file, $ad);
        return $file->storeAs('public/' . $path, $name) !== false ? $path . '/' . $name : false;
    }

    /**
     * @param UploadedFile $file
     * @param Ad $ad
     * @return bool|string uploaded path on success, and false on failure
     */
    private function tryChangeFile(UploadedFile $file, Ad $ad): bool|string {
        $fullPath = $this->tryUploadFile($file, $ad);
        if ($fullPath === false) {
            return false;
        }
        $deleteOldOne = Storage::delete('public/' . $ad->getFilePath());
        if ($deleteOldOne === false) {
            Storage::delete('public/' . $fullPath);
            return false;
        }
        return $fullPath;
    }

    private function getUploadedFilePath(UploadedFile $file, Ad $ad): array {
        $name = $ad->id . '-' . md5($file->getClientOriginalName() . '-' . Str::random()) . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/' . ($ad->advertiser_id ?? 'admin') . '/' . Str::plural(strtolower($ad->getType()));
        return [$path, $name];
    }

    protected function isAdmin(): bool {
        return Auth::user()->isAdmin();
    }
}