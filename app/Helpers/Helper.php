<?php

namespace App\Helpers;

use App\Models\AdBanner;
use App\Models\AdVideo;
use App\Models\BlockItem;
use App\Models\Domains\PublisherDomain;
use Arr;
use Illuminate\Support\Facades\Auth;
use Log;
use Storage;

class Helper {
    public static function option($value, $caption = null, $selected = false, $attributes = []): string {
        if ($selected) {
            $attributes['selected'] = 'selected';
        }
        return view('components.input.option', [
            'value' => $value,
            'caption' => $caption ?? $value,
            '_attributes' => $attributes
        ])->render();
    }

    public static function bannerImg(AdBanner $banner, $width, $height): string {
        return '<img class="banner-preview" src="' . url('storage/' . $banner->file) . '" alt="banner"'
            . ' width="' . e($width) . '" height="' . e($height) . '"/>';
    }

    public static function videoPlayer(AdVideo $video, $width, $height): string {
        return '<video style="object-fit:fill" class="banner-preview" width="' . e($width) . '" height="' . e($height) . '" controls>'
            . '<source src="' . url('storage/' . $video->file) . '" type="' . Storage::disk('public')->mimeType($video->file) . '">'
            . '</video>';
    }

    public static function link($url, $title = 'link', $target = '_blank'): string {
        return "<a href=\"$url\" title=\"$title\" target=\"$target\"><i class=\"material-icons\">open_in_new</i></a>";
    }

    public static function amount($amount, $decimals = 2): string {
        $amount = (float)$amount;
        return ($amount < 0 ? '-' : '') . '$' . number_format(abs($amount), $decimals);
    }

    public static function isAdmin(): bool {
        return Auth::check() && Auth::user()->isAdmin();
    }

    public static function isAdvertiser(): bool {
        return Auth::check() && Auth::user()->isAdvertiser();
    }

    public static function isPublisher(): bool {
        return Auth::check() && Auth::user()->isPublisher();
    }

    public static function isManager($permissions): bool {
        $condition = false;
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isManager()) {
                if (!isset($permissions)) {
                    $condition = true;
                } else {
                    $permissions = Arr::wrap($permissions);
                    foreach ($permissions as $permission) {
                        list($subject, $values) = str_contains($permission, ':') ? explode(':', $permission) : [$permission, ''];
                        $values = !empty($values) ? (str_contains($values, ',') ? explode(',', $values) : [$values]) : [];
                        if ($user->manager->hasAnyPermissions($subject, $values)) {
                            $condition = true;
                            break;
                        }
                    }
                }
            }
        }
        return $condition;
    }

    public static function isUserType(...$types): bool {
        $condition = false;
        if (Auth::check()) {
            $user = Auth::user();
            if (in_array('admin', $types)) {
                $condition = $user->isAdmin();
            }
            if (!$condition && in_array('manager', $types)) {
                $condition = $user->isManager();
            }
            if (!$condition && in_array('advertiser', $types)) {
                $condition = $user->isAdvertiser();
            }
            if (!$condition && in_array('publisher', $types)) {
                $condition = $user->isPublisher();
            }
            if (!$condition) {
                $permissions = collect($types)
                    ->filter(fn($item) => str_starts_with($item, 'manager('))
                    ->map(fn($item) => substr($item, 8, -1))
                    ->first();
                $code = "\$permissions = [$permissions];";
                eval($code);
                $condition = self::isManager($permissions);
            }
        }
        return $condition;
    }

    public static function isDomainValid(PublisherDomain $publisherDomain, $referer): bool {
        $publisherDomain = strtolower($publisherDomain->domain);
        $referer = strtolower($referer);

        Log::info('isDomainValid> PublisherDomain:' . $publisherDomain . ', referer:' . $referer);
        return str_starts_with($referer, $publisherDomain)
            && !BlockItem::whereRaw('? like CONCAT(domain,"%")', $publisherDomain)->exists();
    }

    public static function getUrlDomain(string $url): ?string {
        if (!preg_match('@^https?://@', $url)) {
            $url = 'https://' . $url;
        }
        $host = parse_url($url, PHP_URL_HOST);
        return $host ? implode('.', array_slice(explode('.', $host), -2)) : null;
    }

    public static function phx(string|array $key, string|array $value, string $subject): array|string {
        $key = array_map(fn($k) => self::generatePhx($k), Arr::wrap($key));
        $value = Arr::wrap($value);
        return str_replace($key, $value, $subject);
    }

    public static function hasPhx(string $key, string $subject): bool {
        return str_contains($subject, self::generatePhx($key));
    }

    public static function generatePhx(string $key): string {
        return "%$key%";
    }

    public static function getRandomColors($count = 1, $preferred = true): array {
        $preferredColors = [
            'rgb(54, 162, 235)',
            'rgb(77, 201, 246)',
            'rgb(83, 123, 196)',
            'rgb(22, 106, 143)',
            'rgb(255, 99, 132)',
            'rgb(245, 55, 148)',
            'rgb(246, 112, 25)',
            'rgb(255, 159, 64)',
            'rgb(255, 205, 86)',
            'rgb(172, 194, 54)',
            'rgb(75, 192, 192)',
            'rgb(0, 169, 80)',
            'rgb(153, 102, 255)',
            'rgb(133, 73, 186)',
            'rgb(201, 203, 207)',
            'rgb(88, 89, 91)'
        ];
        $result = $preferred ? array_slice($preferredColors, 0, $count) : [];
        $more = $count - count($result);
        for ($i = 0; $i < $more; $i += 1) {
            $result[] = self::getRandomColor();
        }
        return $result;
    }

    public static function getRandomColor($key = null): string {
        if (isset($key)) {
            $hex = substr(md5($key), 0, 6);
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        } else {
            $r = rand(0, 255);
            $g = rand(0, 255);
            $b = rand(0, 255);
        }
        return "rgb($r,$g,$b)";
    }

    public static function dump($query): string {
        $parts = explode('?', $query->toSql());
        $sql = $parts[0];
        foreach ($query->getBindings() as $index => $binding) {
            $sql .= is_string($binding) ? "\"$binding\"" : (is_bool($binding) ? (int)$binding : $binding);
            $sql .= $parts[$index + 1];
        }
        return $sql;
    }

    public static function proxyCheck($ip): bool {
        // https://github.com/proxycheck/proxycheck.io/blob/master/proxycheck.io.php.function.php

        $apiKey = config('ads.proxycheck_api_key'); // Supply your API key between the quotes if you have one
        $VPN = "1"; // Change this to 1 if you wish to perform VPN Checks on your visitors
        $TAG = "1"; // Change this to 1 to enable tagging of your queries (will show within your dashboard)

        // If you would like to tag this traffic with a specific description place it between the quotes.
        // Without a custom tag entered below the domain and page url will be automatically used instead.
        $Custom_Tag = ""; // Example: $Custom_Tag = "My Forum Signup Page";


        $transportTypeString = "http://";
        $postField = "tag=" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $ch = curl_init($transportTypeString . 'proxycheck.io/v2/' . $ip . '?key=' . $apiKey . '&vpn=' . $VPN);

        $curl_options = array(
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $postField,
            CURLOPT_RETURNTRANSFER => true
        );

        curl_setopt_array($ch, $curl_options);
        $apiJsonResult = curl_exec($ch);
        curl_close($ch);

        $decodedJson = json_decode($apiJsonResult);

        return isset($decodedJson->$ip->proxy) && $decodedJson->$ip->proxy === "yes";
    }
}
