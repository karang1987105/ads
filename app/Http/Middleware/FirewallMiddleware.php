<?php

namespace App\Http\Middleware;

use App\Services\FirewallService;
use Closure;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class FirewallMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $userAgent = strtolower($request->userAgent());
        foreach (self::KNOWN_BOTS as $knownBot) {
            if (str_contains($userAgent, $knownBot)) {
                abort(403);
            }
        }

        if ((new Agent(userAgent: $userAgent))->isRobot($request->userAgent())) {
            abort(403);
        }

        return $next($request);
    }

    private const KNOWN_BOTS = [
        'abacho', 'accona', 'addthis', 'adsbot', 'ahoy', 'ahrefsbot', 'aisearchbot', 'alexa', 'altavista', 'anthill', 'appie',
        'applebot', 'arale', 'araneo', 'araybot', 'ariadne', 'arks', 'aspseek', 'atn_worldwide', 'atomz', 'baiduspider', 'baidu', 'bbot', 'bingbot',
        'bing', 'bjaaland', 'blackwidow', 'botlink', 'bot', 'boxseabot', 'bspider', 'calif', 'ccbot', 'chinaclaw', 'christcrawler', 'cmc/0.01',
        'combine', 'confuzzledbot', 'contaxe', 'coolbot', 'cosmos', 'crawler', 'crawlpaper', 'crawl', 'curl', 'cusco', 'cyberspyder', 'cydralspider',
        'dataprovider', 'digger', 'diibot', 'dotbot', 'downloadexpress', 'dragonbot', 'duckduckbot', 'dwcp', 'easouspider', 'ebiness', 'ecollector',
        'elfinbot', 'esculapio', 'esi', 'esther', 'estyle', 'ezooms', 'facebookexternalhit', 'facebook', 'facebot', 'fastcrawler', 'fatbot', 'fdse',
        'felix ide', 'fetch', 'fido', 'find', 'firefly', 'fouineur', 'freecrawl', 'froogle', 'gammaspider', 'gazz', 'gcreep', 'geona', 'getterrobo-plus',
        'get', 'girafabot', 'golem', 'googlebot', '-google', 'grabber', 'grabnet', 'griffon', 'gromit', 'gulliver', 'gulper', 'hambot', 'havindex', 'hotwired',
        'htdig', 'httrack', 'ia_archiver', 'iajabot', 'idbot', 'informant', 'infoseek', 'infospiders', 'ingrid/0.1', 'inktomi', 'inspectorwww',
        'internet cruiser robot', 'irobot', 'iron33', 'jbot', 'jcrawler', 'jeeves', 'jobo', 'kdd-explorer', 'kit-fireball', 'ko_yappo_robot',
        'label-grabber', 'larbin', 'legs', 'libwww-perl', 'linkedin', 'linkidator', 'linkwalker', 'lockon', 'logo_gif_crawler', 'lycos', 'm2e',
        'majesticseo', 'marvin', 'mattie', 'mediafox', 'mediapartners', 'merzscope', 'mindcrawler', 'mj12bot', 'mod_pagespeed', 'moget', 'motor',
        'msnbot', 'muncher', 'muninn', 'muscatferret', 'mwdsearch', 'nationaldirectory', 'naverbot', 'nec-meshexplorer', 'netcraftsurveyagent',
        'netscoop', 'netseer', 'newscan-online', 'nil', 'none', 'nutch', 'objectssearch', 'occam', 'openstat.ru/bot', 'packrat', 'pageboy', 'parasite',
        'patric', 'pegasus', 'perlcrawler', 'phpdig', 'piltdownman', 'pimptrain', 'pingdom', 'pinterest', 'pjspider', 'plumtreewebaccessor',
        'portalbspider', 'psbot', 'rambler', 'raven', 'rhcs', 'rixbot', 'roadrunner', 'robbie', 'robi', 'robocrawl', 'robofox', 'scooter', 'scrubby',
        'search-au', 'searchprocess', 'search', 'semrushbot', 'senrigan', 'seznambot', 'shagseeker', 'sharp-info-agent', 'sift', 'simbot',
        'site valet', 'sitesucker', 'skymob', 'slcrawler/2.0', 'slurp', 'snooper', 'solbot', 'speedy', 'spider_monkey', 'spiderbot/1.0',
        'spiderline', 'spider', 'suke', 'tach_bw', 'techbot', 'technoratisnoop', 'templeton', 'teoma', 'titin', 'topiclink', 'twitterbot', 'twitter',
        'udmsearch', 'ukonline', 'unwindfetchor', 'url_spider_sql', 'urlck', 'urlresolver', 'valkyrie libwww-perl', 'verticrawl', 'victoria',
        'void-bot', 'voyager', 'vwbot_k', 'wapspider', 'webbandit/1.0', 'webcatcher', 'webcopier', 'webfindbot', 'webleacher', 'webmechanic',
        'webmoose', 'webquest', 'webreaper', 'webspider', 'webs', 'webwalker', 'webzip', 'wget', 'whowhere', 'winona', 'wlm', 'wolp', 'woriobot', 'wwwc',
        'xget', 'xing', 'yahoo', 'yandexbot', 'yandexmobilebot', 'yandex', 'yeti', 'zeus'
    ];
}