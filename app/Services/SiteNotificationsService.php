<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Domains\AdvertiserDomain;
use App\Models\Domains\PublisherDomain;
use App\Models\Place;
use App\Models\TicketThread;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Auth;
use Illuminate\Database\Query\Builder;
use Str;

class SiteNotificationsService {
    private array $notifications = [];

    private function load(bool $reset = false) {
        if ($reset === true || empty($this->notifications)) {
            $this->notifications = [];
            array_map(fn($key) => $this->setValue($key), self::getKeys());
        }
    }

    public function getItems() {
        $this->load();
        $o = '';
        foreach (self::getKeys() as $key) {
            if ($this->notifications[$key] > 0) {
                $o .= view('components.sitenotifications.row', self::getItemData($key, $this->notifications[$key]))->render();
            }
        }
        return $o;
    }

    public function getCount(): int {
        $this->load();
        return array_sum($this->notifications);
    }

    private static function getKeys(): array {
        $keys = [];
        $user = Auth::user();
        if ($user->isManager()) {
            $keys[] = 'new_tickets';
            $keys[] = 'open_tickets';
            if ($user->isAdmin()) {
                $keys[] = 'pending_users';
                $keys[] = 'pending_domains';
                $keys[] = 'pending_ads';
                $keys[] = 'pending_payments';
                $keys[] = 'pending_places';
            }
        } else {
            $keys[] = 'open_tickets';
        }
        return $keys;
    }

    /**
     * @param string('new_tickets', 'open_tickets', 'pending_users', 'pending_domains', 'pending_ads', 'pending_payments', 'pending_places') $type
     */
    private static function getItemData($type, $value): array {
        switch ($type) {
            case 'new_tickets':
                return [
                    'icon' => 'upcoming',
                    'title' => 'New ' . Str::plural('Ticket', $value),
                    'description' => $value > 1 ? "There are $value new tickets!" : "There is a new ticket!",
                ];
            case 'open_tickets':
                return [
                    'icon' => 'mark_email_unread',
                    'title' => 'New ' . Str::plural('Reply', $value),
                    'description' => $value > 1 ? "There are $value unanswered replies!" : "There is an unanswered reply!",
                ];
            case 'pending_users':
                return [
                    'icon' => 'badge',
                    'title' => 'Pending ' . Str::plural('User', $value),
                    'description' => $value > 1 ? "There are $value new users waiting for approval!" : "There is an new user waiting for approval!",
                ];
            case 'pending_domains':
                return [
                    'icon' => 'http',
                    'title' => 'Pending ' . Str::plural('Domain', $value),
                    'description' => $value > 1 ? "There are $value new domains waiting for approval!" : "There is an new domain waiting for approval!",
                ];
            case 'pending_ads':
                return [
                    'icon' => 'ads_click',
                    'title' => 'Pending ' . Str::plural('Campaign', $value),
                    'description' => $value > 1 ? "There are $value new advertisements waiting for approval!" : "There is an new advertisement waiting for approval!",
                ];
            case 'pending_payments':
                return [
                    'icon' => 'payment',
                    'title' => 'New Withdrawal ' . Str::plural('Request', $value),
                    'description' => "There are $value new withdrawal " . Str::plural('request', $value) . '!',
                ];
            case 'pending_places':
                return [
                    'icon' => 'developer_mode',
                    'title' => 'Pending ' . Str::plural('Place', $value),
                    'description' => ($value > 1 ? "There are $value places" : "There is a place") . ' waiting for approval!',
                ];
        }
        return [];
    }

    /**
     * @param string('new_tickets', 'open_tickets', 'pending_users', 'pending_domains', 'pending_ads', 'pending_payments', 'pending_places') $type
     */
    private function setValue($type) {
        $value = null;
        switch ($type) {
            case 'new_tickets':
                $value = TicketThread::closed(false)->unanswered()->count();
                break;
            case 'open_tickets':
                $value = TicketThread::closed(false)->involvesMe()->count();
                break;
            case 'pending_users':
                $value = User::inactive()->count();
                break;
            case 'pending_domains':
                $value = AdvertiserDomain::unapproved()->count() + PublisherDomain::unapproved()->count();
                break;
            case 'pending_ads':
                $value = Ad::unapproved()->count();
                break;
            case 'pending_payments':
                $value = WithdrawalRequest::whereIn('id', static function (Builder $q) {
                    $q->select('withdrawals_requests.id')
                        ->from('withdrawals_requests')
                        ->join('invoices', 'invoices.withdrawal_request_id', '=', 'withdrawals_requests.id')
                        ->leftJoin('payments', 'payments.id', '=', 'invoices.payment_id')
                        ->whereNull('payments.confirmed_at')
                        ->groupBy('withdrawals_requests.id');
                })->count();
                break;
            case 'pending_places':
                $value = Place::unapproved()->count();
                break;
        }
        if (isset($value)) {
            $this->notifications[$type] = $value;
        }
    }
}
