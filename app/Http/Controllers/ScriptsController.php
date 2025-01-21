<?php

namespace App\Http\Controllers;

use App\Services\Vast\Ad;
use App\Services\Vast\Creative;
use App\Services\Vast\Creatives;
use App\Services\Vast\InLine;
use App\Services\Vast\Linear;
use App\Services\Vast\MediaFiles;
use App\Services\Vast\TrackingEvents;
use App\Services\Vast\VideoClicks;
use App\Services\Vast\Vast;

use App\Helpers\Helper;
use App\Jobs\AdEventQueue;
use App\Models\BlockItem;
use App\Models\Campaign;
use App\Models\Country;
use App\Models\Place;
use App\Models\Setting;
use App\Services\FirewallService;
use Illuminate\Http\Request;
use Log;
use Storage;

class ScriptsController extends Controller {

    public function init(string $placeUid) {
        $url = route('scripts.show', ['uuid' => $placeUid]);
        return response(
            ";(function(){var d=document,url='$url';"
            . "scr=d.createElement('script');"
            . "scr.src=url+(url.includes('?')?'&':'?')+'t='+Date.now()+'&rf='+encodeURIComponent(document.referrer);"
            . "d.head.appendChild(scr)})();"
        )->header('Content-Type', 'text/javascript');
    }

    public function show(string $placeUid, Request $request) {
        if (Setting::value(Setting::CAMPAIGNS_STOP)) {
            abort(503);
        }

        if ($request->has('t') && $request->has('rf')) {
            /**
             * @var Place $place
             * @var Campaign $campaign
             */
            $place = Place::uuid($placeUid);
            if (!$place) {
                Log::info("Place is not valid: $placeUid");
                abort(404);
            }

            $ip = $request->ip();

            $referrer = rawurldecode($request->get('rf'));
            if ($referrer && !$this->isValidReferrer($referrer, $place->id, $ip)) {
                Log::info("Referrer ($referrer) blocked or not valid.");

                $cookie = cookie(
                    $this->getBlockedReferrerVisitorCookieName(),
                    md5(time()),
                    config('ads.blocked_referrer_expiry')
                );

                return response(status: 204)->withCookie($cookie);
            }

            $this->requiresWhiteVisitor($place->id, $ip, $request);

            if ($place->isApproved() && $place->adType->active && $place->domain->isApproved() && $place->domain->publisher->isActive()) {

                $this->requiresValidDomain($place, $request);

                $country = $this->checkCountry($ip, $place->id);

                $firewall = app(FirewallService::class);
                $isMobile = $firewall->isMobileRequest($request->userAgent());
                $checkProxy = fn() => $firewall->isProxyRequest($ip, $country->getCurrentTimestamp(), $request->t / 1000);

                if ($place->adType->isBanner()) {
                    $result = $place->adType->firstCampaignOnQueue($place->domain->category->id, $country->id, $place->id, $ip, $isMobile);
                    if ($result->isNotEmpty()) {

                        $campaign = $result->first();
                        if (!$campaign->proxy && $checkProxy()) {
                            $campaign = $result->firstWhere('proxy', true);
                        }
                        if ($campaign) {
                            $campaign->refresh();

                            // Impress CPC on click
                            $campaign->impressed($place->id, $ip, true, !$place->adType->isCPC());

                            $rand = AdsHashesController::registerNewAdHash($campaign->id, $place->id, $ip);
                            $data = [
                                'ad' => $campaign->ad,
                                'campaign' => $campaign,
                                'rand' => $rand,
                                'dom_id' => 's_' . md5($placeUid),
                                'trigger_url' => route('scripts.trigger', [
                                    'campaign' => $campaign->uuid,
                                    'place' => $place->uuid,
                                    'h' => $rand
                                ])
                            ];
                            if (!$campaign->ad->isThirdParty()) {
                                $view = 'components.scripts.banner';

                                // TODO: cache logos
                                $data['logo'] = view('components.scripts.logos.banner-backlink', ['id' => $rand])->render();
                                $data['v_controls'] = view('components.scripts.logos.video-controls', ['id' => $rand])->render();

                            } else {
                                $view = 'components.scripts.thirdparty';
                                $data['click_url'] = $campaign->ad->getUrl(true);
                                $data['code'] = $campaign->ad->thirdParty->code;
                            }
                            return response()->view($view, $data)->header('Content-Type', 'text/javascript');
                        }
                    }

                    Log::info("No banner campaign available for Place#$place->id");

                } elseif ($place->adType->isVideo()) {
                    $result = $place->adType->firstCampaignOnQueue($place->domain->category->id, $country->id, $place->id, $ip, $isMobile);
                    if ($result->isNotEmpty()) {

                        $campaign = $result->first();
                        if (!$campaign->proxy && $checkProxy()) {
                            $campaign = $result->firstWhere('proxy', true);
                        }
                        if ($campaign) {
                            $campaign->refresh(); //select * from games where campaign_id=6 and account=9377009

                            // Impress CPC on click
                            $campaign->impressed($place->id, $ip, true, !$place->adType->isCPC());

                            $rand = AdsHashesController::registerNewAdHash($campaign->id, $place->id, $ip);

                            $data = [
                                'ad' => $campaign->ad,
                                'rand' => $rand,
                                'uuid'=> $placeUid, // comment;
                                'dom_id' => 's_' . md5($placeUid)
                            ];
                            if (!$campaign->ad->isThirdParty()) {
                                if (config('ads.ads.videos.player_type') === 'vast') {
                                    $view = 'components.scripts.video-vast';
                                    $data['vast_url'] = route('scripts.vast', ['campaign' => $campaign->uuid, 'place' => $place->uuid]);
                                    $data['click_url'] = $campaign->ad->getUrl(true);
                                } else {
                                    $view = 'components.scripts.video-html5';
                                    $data['video_url'] = url('storage/' . $campaign->ad->video->file);
                                    $data['video_mime'] = Storage::disk('public')->mimeType($campaign->ad->video->file);
                                    $data['click_url'] = $campaign->ad->getUrl(true);
                                    $data['trigger_url'] = route('scripts.trigger', ['campaign' => $campaign->uuid, 'place' => $place->uuid, 'h' => $data['rand']]);
                                    $data['iOS'] = $firewall->getAgent($request->userAgent())->is('iOS');
                                }

                                // TODO: cache logos
                                $data['logo'] = view('components.scripts.logos.video-backlink', ['id' => $rand])->render();
                                $data['v_controls'] = view('components.scripts.logos.video-controls', ['id' => $rand])->render();
                            } else {
                                $view = 'components.scripts.thirdparty';
                                $data['code'] = $campaign->ad->thirdParty->code;
                                $data['trigger_url'] = route('scripts.trigger', ['campaign' => $campaign->uuid, 'place' => $place->uuid, 'h' => $data['rand']]);
                            }
                            return response()->view($view, $data)->header('Content-Type', 'text/javascript');
                        }
                    }

                    Log::info("No video campaign available for Place#$place->id");
                }
            }
        }
        abort(204);
    }

    public  function trigger($placeUid, $campaignUid, Request $request) {
        $response = response('')->header('Cache-Control', 'no-store');

        if (Setting::value(Setting::CAMPAIGNS_STOP)) {
            return $response;
        }

        if ($request->has('t','h')) {
            $job = new AdEventQueue($placeUid, $campaignUid, [
                'ip' => $request->ip(),
                'domain' => $request->header('referer'),
                'time' => $request->input('t') / 1000,
                'hash' => $request->input('h'),
                'useragent' => $request->userAgent(),
                'click' => $request->has('c')
            ]);
            dispatch($job);
        }

        return $response;
    }

    public  function test(Request $request) {
        Log::info(json_encode($request->all()));
    }

    public  function vast(string $placeUid, string $campaignUid, Request $request) {
        if ($request->has('t') && $request->has('h')) {
            /**
             * @var Place $place
             * @var Campaign $campaign
             */
            $place = Place::uuid($placeUid);
            $campaign = Campaign::uuid($campaignUid);
            if ($place === null || !$place->isApproved() || $campaign === null || !$campaign->isActive()) {
                Log::info("Invalid Place or Campaign requested for VAST");
                abort(404);
            }
            if (!config('app.debug') && !Helper::isDomainValid($place->domain, $request->header('referer'))) {
                Log::info("Place domain ({$place->domain->domain}) doesn't match client referer domain {$request->header('referer')} for Place#$place->id");
                abort(404);
            }

            $triggerUrl = function ($click) use ($request, $place, $campaign) {
                $data = ['campaign' => $campaign->uuid, 'place' => $place->uuid, 't' => $request->t, 'h' => $request->h];
                if ($click) {
                    $data['c'] = 1;
                }
                return route('scripts.trigger', $data);
            };


            $vast = new Vast();
            $vast->addError(route('scripts.test', ['error' => 'vast']));
            {
                $ad = new Ad();
                $ad->setId($campaign->uuid);
                $ad->setSequence('1');
                $ad->setAdType('video');
                {
                    $inline = new InLine();
                    $inline->setAdSystem(config('app.name'));
                    $inline->setAdTitle($campaign->uuid);
                    $inline->setAdServingId($campaign->uuid);
                    $inline->addError(route('scripts.test', ['error' => 'inline']));
                    {
                        $creatives = new Creatives();
                        {
                            $creative = new Creative();
                            $creative->addUniversalAdId(config('app.name'), $campaign->uuid);
                            {
                                $linear = new Linear();
                                {
                                    $mediaFiles = new MediaFiles();
                                    $mediaFiles->addMediaFile(
                                        'progressive',
                                        Storage::disk('public')->mimeType($campaign->ad->video->file),
                                        $campaign->ad->adType->width,
                                        $campaign->ad->adType->height,
                                        url('storage/' . $campaign->ad->video->file)
                                    );
                                    $linear->setMediaFiles($mediaFiles);

                                    $videoClicks = new VideoClicks();
                                    $videoClicks->setClickThrough($campaign->ad->getUrl(true));
                                    $videoClicks->setClickTracking($triggerUrl(true));
                                    $linear->setVideoClicks($videoClicks);

                                    $trackingEvents = new TrackingEvents();
                                    $trackingEvents->addTracking($triggerUrl(false), 'progress', self::formatOffset(config('ads.ads.videos.player_impression_delay')) );
                                    $linear->setTrackingEvents($trackingEvents);
                                }
                                $creative->setLinear($linear);
                            }
                            $creatives->addCreative($creative);
                        }
                        $inline->setCreatives($creatives);
                    }
                }
                $vast->addAd($ad->setInLine($inline));
            }
            return response($vast->getXML())
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, HEAD, OPTIONS')
                ->header('Content-Type', 'text/xml')
                ->header('Cache-Control', 'no-store');
        }
        abort(400);
    }

    private static function formatOffset($offset): string {
        $offset /= 1000;
        $milliseconds = str_replace("0.", '', round($offset - floor($offset), 3));
        $hours = $offset > 3600 ? floor($offset / 3600) : 0;
        $seconds = $offset % 3600;
        return str_pad($hours, 2, '0', STR_PAD_LEFT) . gmdate(':i:s', $seconds) . '.'
            . str_pad($milliseconds, 3, '0', STR_PAD_RIGHT);
    }

    private function isValidReferrer(string $referrer, int $placeId, string $ip): bool {
        $referrerDomain = Helper::getUrlDomain($referrer);
        if (!$referrerDomain || BlockItem::where('domain', $referrerDomain)->exists()) {

            if (config('ads.blocked_referrer_database_check')) {
                \DB::table('blocked_referrers')->insertOrIgnore([
                    'ip' => ip2long($ip),
                    'place_id' => $placeId,
                    'created_at' => now()
                ]);
            }

            return false;
        }

        return true;
    }

    private function requiresWhiteVisitor(int $placeId, string $ip, Request $request): void {
        if ($request->hasCookie($this->getBlockedReferrerVisitorCookieName())) {
            Log::info("Visitor ($ip) blocked because from a blocked referrer.");
            abort(204);
        }

        if (config('ads.blocked_referrer_database_check')) {
            $exists = \DB::table('blocked_referrers')
                ->where('ip', ip2long($ip))
                ->where('place_id', $placeId)
                ->where('created_at', '>=', now()->subMinutes(config('ads.blocked_referrer_expiry')))
                ->exists();

            if ($exists) {
                Log::info("Visitor ($ip) blocked because from a blocked referrer.");
                abort(204);
            }
        }
    }

    private function getBlockedReferrerVisitorCookieName(): string {
        return md5(config('app.name')) . '_brv';
    }

    private function requiresValidDomain(Place $place, Request $request): void {
        if (!config('app.debug') && !Helper::isDomainValid($place->domain, $request->header('referer'))) {
            Log::info("Place domain ({$place->domain->domain}) doesn't match client referer domain {$request->header('referer')} for Place#$place->id");
            abort(404);
        }
    }

    private function checkCountry(string $ip, int $placeId): Country {
        if (!config('app.debug')) {
            $country = Country::getCodeByIp($ip);
        } else {
            $country = Country::find(config('ads.localhost_country'));
        }

        if ($country === null) {
            Log::info("Unknown/Invalid client ip [" . $ip . "] for Place#$placeId");
            abort(204);
        }

        if ($country->hidden === 1) {
            Log::info("Hidden country request from client ip [" . $ip . "] for Place#$placeId");
            abort(204);
        }

        return $country;
    }

    public static function cleanupBlockedReferrers(): void {
        \DB::table('blocked_referrers')
            ->where('created_at', '<', now()->subMinutes(config('ads.blocked_referrer_expiry')))
            ->delete();
    }
}
