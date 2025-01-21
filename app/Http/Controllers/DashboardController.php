<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Records\RecordsController;
use App\Models\Place;
use App\Models\InvoiceCampaign;
use App\Models\User;
use App\Models\UserAdvertiser;
use App\Models\UserManager;
use App\Models\UserPublisher;
use Auth;

class DashboardController extends Controller {

    private RecordsController $recordsController;

    public function __construct() {
        $this->recordsController = new RecordsController();
    }

    public function index() {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $data = $this->getAdminViewData($user);
        } else {
            $data = match ($user->type) {
                'Manager' => $this->getManagerViewData($user->manager),
                'Advertiser' => $this->getAdvertiserViewData($user->advertiser),
                'Publisher' => $this->getPublisherViewData($user->publisher),
            };
        }

        $data['statCards'] = ' ';
        $data['statCards'] .= $this->getTotalImpressionsCardData();
        $data['statCards'] .= $this->getTotalClicksCardData();
        $data['statCards'] .= $this->getTotalCTRCardData();
        $data['totalDataEachCountries'] = $this->getTotalDataWithEachCountries();
        
        if ($user->isManager()) {
            $data['statCards'] .= ''
                . $this->getActiveCampaignsCardData(null)
                . $this->getTotalAdvertisersBalanceCardData()
                . $this->getTotalPublishersPaidCardData()
                . $this->getPublishersBalanceCardData()
                . $this->getTotalActivePlacesCardData(null)
                . $this->getTotalAdvertisersCardData()
                . $this->getTotalPublishersCardData();
            $data['activePlacesCountEachCountry'] = $this->getActivePlacesCountryEachCountry(null);
            $data['activePlacesCountStatus'] = true;
            $data['activeCampaignsCountEachCountry'] = $this->getActiveCampaignsCountEachCountry(null);
            $data['activeCampaignsCountStatus'] = true;
            $data['totalAdvertisersCountEachCountry'] = $this->getTotalAdvertisersEachCountry();
            $data['totalAdvertisersCountStatus'] = true;
            $data['totalPublishersCountEachCountry'] = $this->getTotalPublishersEachCountry();
            $data['totalPublishersCountStatus'] = true;
            $data['totalAdvertisersBalanceEachCountry'] = $this->getTotalAdvertisersBalanceEachCountry();
            $data['totalAdvertisersBalanceStatus'] = true;
            $data['totalPublishersBalanceEachCountry'] = $this->getTotalPublishersBalanceEachCountry();
            $data['totalPublishersBalanceStatus'] = true;
        }

        if ($user->isPublisher()) {
            $data['statCards'] .= $this->getTotalActivePlacesCardData($user);
            $data['activePlacesCountEachCountry'] = $this->getActivePlacesCountryEachCountry(null);
            $data['activePlacesCountStatus'] = true;
            $data['activeCampaignsCountEachCountry'] = $this->getActiveCampaignsCountEachCountry($user);
            $data['activeCampaignsCountStatus'] = false;
            $data['totalAdvertisersCountEachCountry'] = [];
            $data['totalAdvertisersCountStatus'] = false;
            $data['totalPublishersCountEachCountry'] = [];
            $data['totalPublishersCountStatus'] = false;
            $data['totalAdvertisersBalanceEachCountry'] = [];
            $data['totalAdvertisersBalanceStatus'] = false;
            $data['totalPublishersBalanceEachCountry'] = [];
            $data['totalPublishersBalanceStatus'] = false;
        }

        if ($user->isAdvertiser()) {
            $data['statCards'] .= $this->getActiveCampaignsCardData($user);
            $data['activePlacesCountStatus'] = false;
            $data['activePlacesCountEachCountry'] = [];
            $data['activeCampaignsCountEachCountry'] = $this->getActiveCampaignsCountEachCountry($user);
            $data['activeCampaignsCountStatus'] = true;
            $data['totalAdvertisersCountEachCountry'] = [];
            $data['totalAdvertisersCountStatus'] = false;
            $data['totalPublishersCountEachCountry'] = [];
            $data['totalPublishersCountStatus'] = false;
            $data['totalAdvertisersBalanceEachCountry'] = [];
            $data['totalAdvertisersBalanceStatus'] = false;
            $data['totalPublishersBalanceEachCountry'] = [];
            $data['totalPublishersBalanceStatus'] = false;
        }
        // dd($data);
        return view('dashboard', $data);
    }

    private function getAdminViewData(User $user): array {
        $data = [];
        $data['active_ads'] = $this->recordsController->index('ads');
        $data['active_places'] = $this->recordsController->index('places');
        return ['admin' => $data];
    }

    private function getManagerViewData(UserManager $manager): array {
        $data = [];
        $data['active_ads'] = $this->recordsController->index('ads');
        return ['manager' => $data];
    }

    private function getAdvertiserViewData(UserAdvertiser $advertiser): array {
        $data = [];
        $data['balance'] = $advertiser->getBalance();
        $data['active_ads'] = $this->recordsController->index('ads');
        return ['advertiser' => $data];
    }

    private function getPublisherViewData(UserPublisher $publisher): array {
        $data = [];
        $data['balance'] = $publisher->getBalance();
        $data['active_places'] = $this->recordsController->index('places');
        return ['publisher' => $data];
    }

    private function getTotalImpressionsCardData(): string {
        $data = $this->recordsController->getTotalImpressionsCardData();
        if (!empty($data)) {
            return view('partials.stat-card', $data)->render();
        }
        return '';
    }

    private function getTotalClicksCardData(): string {
        $data = $this->recordsController->getTotalClicksCardData();
        if (!empty($data)) {
            return view('partials.stat-card', $data)->render();
        }
        return '';
    }

    private function getTotalCTRCardData(): string {
        $data = $this->recordsController->getTotalCTRCardData();
        if (!empty($data)) {
            return view('partials.stat-card', $data)->render();
        }
        return '';
    }

    private function getTotalDataWithEachCountries(): array {
        return $this->recordsController->getTotalDataWithEachCountries();
    }

    private function getTotalActivePlacesCardData(?User $user): string {
        return view('partials.stat-card', [
            'title' => 'Total Active Places',
            'value' => number_format(Place::activePlacesCount($user))
        ])->render();
    }

    private function getActivePlacesCountryEachCountry(?User $user): array {
        return (Place::activePlacesCountEachCountry($user));
    }

    private function getActiveCampaignsCountEachCountry(?User $user): array {
        return $this->recordsController->activeCampaignsCountEachCountry($user);
    }

    private function getTotalAdvertisersCardData(): string {
        return view('partials.stat-card', [
            'title' => 'Total Registered Advertisers',
            'value' => number_format(UserAdvertiser::count())
        ])->render();
    }

    private function getTotalAdvertisersEachCountry(): array {
        return $this->recordsController->getTotalAdvertisersEachCountry();
    }

    private function getTotalPublishersEachCountry(): array {
        return $this->recordsController->getTotalPublishersEachCountry();
    }
    private function getTotalPublishersCardData(): string {
        return view('partials.stat-card', [
            'title' => 'Total Registered Publishers',
            'value' => number_format(UserPublisher::count())
        ])->render();
    }

    private function getActiveCampaignsCardData(?User $user): string {
        $data = $this->recordsController->getActiveCampaignsData($user);
        if (!empty($data)) {
            return view('partials.stat-card', $data)->render();
        }
        return '';
    }

    private function getTotalAdvertisersBalanceCardData(): string {
        $data = $this->recordsController->getTotalAdvertisersBalanceData();
        if (!empty($data)) {
            return view('partials.stat-card', $data)->render();
        }
        return '';
    }

    private function getTotalAdvertisersBalanceEachCountry(): array {
        return $this->recordsController->getTotalAdvertisersBalanceEachCountry();
    }

    private function getTotalPublishersPaidCardData(): string {
        $data = $this->recordsController->getTotalPublishersPaidData();
        if (!empty($data)) {
            return view('partials.stat-card', $data)->render();
        }
        return '';
    }

    private function getPublishersBalanceCardData(): string {
        $data = $this->recordsController->getPublishersBalanceData();
        if (!empty($data)) {
            return view('partials.stat-card', $data)->render();
        }
        return '';
    }

    private function getTotalPublishersBalanceEachCountry(): array {
        return $this->recordsController->getTotalPublishersBalanceEachCountry();
    }
}
