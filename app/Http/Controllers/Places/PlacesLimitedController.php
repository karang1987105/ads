<?php

namespace App\Http\Controllers\Places;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AdType;
use App\Models\Place;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;

class PlacesLimitedController extends Controller
{
    public function index(Request $request)
    {

        $headerOptions = [
            'title' => 'Available Places'
        ];

        $domainsExists = Auth::user()->publisher->domains()
            ->whereNotNull('approved_at')
            ->whereNotNull('category_id')
            ->exists();

        if ($domainsExists) {
            $headerOptions['add'] = '<form data-name="add" class="d-none"></form>';
            $headerOptions['add_url'] = route('publisher.places.create', absolute: false);
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
            'page_title' => 'Manage Places',
            'slot' => view('components.list.list', [
                'key' => 'all',
                'header' => view('components.list.header', $headerOptions),
                'body' => $this->list($request)
            ])
        ]);
    }

    public function list(Request $request)
    {
        $publisher = Auth::user()->publisher;
        $places = $publisher->places()->page($request->query->get('page'), ['places.*']);
        return view('components.list.body', [
            'url' => route('publisher.places.list', ['publisher' => $publisher->user_id], false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Title', 'AD Type', 'AD Size', 'Domain', 'Approved', 'Actions'],
            'rows' => $places->getCollection()->toString(fn($place) => $this->listRow($place)->render()),
            'pagination' => $places->links()
        ]);
    }

    protected function listRow(Place $place)
    {
        $isApproved = $place->isApproved();
        return view('components.list.row', [
            'id' => $place->id,
            'columns' => [
                $place->title,
                $place->adType->name,
                $place->adType->getSize(),
				$place->domain->domain,
                $isApproved ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
            ],
            'show' => ['url' => route('publisher.places.show', ['place' => $place->id], false)],
            'edit' => ['url' => route('publisher.places.edit', ['place' => $place->id], false)],
            'delete' => ['url' => route('publisher.places.destroy', ['place' => $place->id], false)]
        ]);
    }

    public function show(Place $place)
    {
        return view('components.list.row-details', [
			'rows' => [
                ['caption' => 'Place ID:', 'value' => $place->id],
				['caption' => 'Domain Category:', 'value' => $place->domain->category->title],
                [
                    'caption' => 'AD Code:',
                    'full' => true,
                    'value' => '<pre>&lt;script id="s_' . md5($place->uuid) . '" src="' . route('scripts.init', ['uuid' => $place->uuid]) . '"&gt;&lt;/script&gt;</pre>'
                ]
            ]
        ]);
    }

    private function form(Place $place = null)
    {
        $publisher = Auth::user()->publisher;
        $adtype_options = AdType::active()->get()
        ->toString(fn($adType) => Helper::option($adType->id, "$adType->name " . $adType->getSize(), isset($place) && $place->ad_type_id === $adType->id));
        $domain_options = $publisher->domains()->whereNotNull('approved_at')->whereNotNull('category_id')->get()
		->toString(fn($domain) => Helper::option($domain->id, "$domain->domain @ Category: " . $domain->category->title, isset($place) && $place->domain_id === $domain->id));

        $params = [
            'route' => 'publisher',
            'adtype_options' => $adtype_options,
            'domain_options' => $domain_options
        ];
        if ($place != null) {
            $params['place'] = $place;
        }
        return view('components.places.form', $params);
    }

    public function create()
    {
        return $this->form();
    }

    public function edit(Place $place)
    {
        return $this->form($place);
    }

    public function destroy(Place $place)
    {
        return $place->delete();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'domain_id' => 'required|exists:App\Models\Domains\PublisherDomain,id',
            'ad_type_id' => 'required|exists:App\Models\AdType,id',
        ]);

        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        $samePlaceExists = Place::query()
            ->where('ad_type_id', $request->get('ad_type_id'))
            ->where('domain_id', $request->get('domain_id'))
            ->whereNull('deleted_at')
            ->exists();

        if ($samePlaceExists) {
            return $this->failure(['form' => 'You already have a place for this ad type and domain!']);
        }

        $invalidDomain = Auth::user()->publisher->domains()->find($request->domain_id) === null;

        if ($invalidDomain) {
            return $this->failure(['domain_id' => 'Domain is not valid!']);
        }

        $place = Place::create([
            'title' => $request->title,
            'domain_id' => $request->domain_id,
            'ad_type_id' => $request->ad_type_id,
            'uuid' => Str::uuid()->toString()
        ]);

        return $this->success($this->listRow($place)->render());
    }

    public function update(Place $place, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'domain_id' => 'required|exists:App\Models\Domains\PublisherDomain,id',
            'ad_type_id' => 'required|exists:App\Models\AdType,id',
        ]);

        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        $samePlaceExists = Place::query()
            ->where('ad_type_id', $request->get('ad_type_id'))
            ->where('domain_id', $request->get('domain_id'))
            ->whereNull('deleted_at')
            ->where('id', '!=', $place->id)
            ->exists();

        if ($samePlaceExists) {
            return $this->failure(['form' => 'You already have a place for this ad type and domain!']);
        }

        $invalidDomain = Auth::user()->publisher->domains()->find($request->domain_id) === null;

        if ($invalidDomain) {
            return $this->failure(['domain_id' => 'Domain is not valid!']);
        }

        $request['approved_by_id'] = null;
        $request['approved_at'] = null;

        $place->update($request->all());

        return $this->success($this->listRow($place->fresh())->render());
    }
}