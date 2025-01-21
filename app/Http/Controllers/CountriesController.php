<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CountriesController extends Controller {
    public function index(Request $request) {
        $tables = view('components.list.list', ['key' => 'Tier 1', 'header' => $this->listHeader('Tier 1'), 'body' => $this->list('Tier 1', $request)])->render()
            . view('components.list.list', ['key' => 'Tier 2', 'header' => $this->listHeader('Tier 2'), 'body' => $this->list('Tier 2', $request)])->render()
            . view('components.list.list', ['key' => 'Tier 3', 'header' => $this->listHeader('Tier 3'), 'body' => $this->list('Tier 3', $request)])->render()
            . view('components.list.list', ['key' => 'Tier 4', 'header' => $this->listHeader('Tier 4'), 'body' => $this->list('Tier 4', $request)])->render();
        return view('layouts.app', [
            'page_title' => 'Manage Geo Profiles',
            'title' => 'Manage Geo Profiles',
            'slot' => $tables
        ]);
    }

    public function list($tier, Request $request) {
        $items = $this->search(Country::tier($tier))->orderBy('hidden', 'DESC')->orderBy('name', 'ASC')->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('admin.countries.list', ['tier' => $tier], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Country Name', 'Country ID', 'Active', 'Actions'],
            'rows' => $items->getCollection()->toString(fn($item) => $this->listRow($item)->render()),
            'pagination' => $items->links()
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = request();
            $this->whereString('countries.name', $req->name, $q);
            $this->whereEquals('countries.id', $req->id, $q);
        }
        return $q;
    }

    private function listRow(Country $item) {
        return view('components.list.row', [
            'id' => $item->id,
            'columns' => [$item->name, $item->id, $item->hidden ? '<a class="declined">✘</a>' : '<a class="approved">✔</a>'],
            'extra' => [
                view('components.list.row-action', [
                    'click' => 'Ads.item.updateRow(this)',
                    'title' => $item->hidden ? 'Accept' : 'Decline',
                    'icon' => $item->hidden ? 'task_alt' : 'block',
                    'url' => route('admin.countries.visibility', ['country' => $item->id, 'hide' => intval(!$item->hidden)], false)
                ])->render(),
                view('components.list.row-action', [
                    'click' => 'Ads.item.openExtra(this)',
                    'title' => 'Move',
                    'icon' => 'input',
                    'url' => route('admin.countries.edit', ['country' => $item->id], false)
                ])				
            ]
        ]);
    }

    private function form(Country $country) {
        return view('components.countries.form', compact('country'));
    }

    private function searchForm($tier) {
        return view('components.countries.search-form', compact('tier'));
    }

    private function listHeader($tier) {
        return view('components.list.header', ['title' => $tier, 'search' => $this->searchForm($tier)]);
    }

    public function edit(Country $country) {
        return $this->form($country);
    }

    private function validateFields(Request $request, array|null $fields = null): \Illuminate\Contracts\Validation\Validator|array {
        $allFields = ['category' => 'required|in:Tier 1,Tier 2,Tier 3,Tier 4',];
        if (isset($fields)) {
            $allFields = array_filter($allFields, fn($key) => in_array($key, $fields), ARRAY_FILTER_USE_KEY);
        }
        return Validator::make($request->all(), $allFields);
    }

    public function update(Request $request, Country $country) {
        $validator = $this->validateFields($request);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        $currentTier = $country->category;
        try {
            $country->update($request->all());
            return $this->success($country->category == $currentTier ? $this->listRow($country)->render() : '');
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function visibility(Country $country, $hide) {
        if ($hide && !$country->hidden) {
            $country->hidden = true;
        } elseif (!$hide && $country->hidden) {
            $country->hidden = false;
        }
        $country->save();
        return $this->listRow($country);
    }
}
