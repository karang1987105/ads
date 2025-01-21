<?php


namespace App\Http\Controllers\Categories;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Domains\PublisherDomain;
use App\Models\InvoiceCampaign;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoriesController extends Controller {
    public function index(Request $request) {
        return view('layouts.app', [
            'page_title' => 'Manage Categories',
            'title' => 'Manage Categories',
            'slot' => view('components.list.list', ['key' => 'all', 'header' => $this->listHeader(), 'body' => $this->list($request)])
        ]);
    }

    public function activate(Category $category, $active) {
        if ($active && !$category->active) {
            $category->active = true;
        } elseif (!$active && $category->active) {
            $category->active = false;
        }
        $category->save();
        return $this->listRow($category);
    }

    public function list(Request $request) {
        $categories = $this->search(Category::query())->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('admin.categories.list', absolute: false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Title', 'CPC', 'CPM', 'CPV', 'Revenue Share', 'Public', 'Actions'],
            'rows' => $categories->getCollection()->toString(fn($c) => $this->listRow($c)->render()),
            'pagination' => $categories->links()
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = request();
            $this->whereString('categories.title', $req->title, $q);
            $this->whereBoolean('categories.active', $req->active, $q);
        }
        return $q;
    }

    private function listRow(Category $category) {
        return view('components.list.row', [
            'id' => $category->id,
            'columns' => [
                $category->title,
                round($category->cpc, 5),
                round($category->cpm, 5),
                round($category->cpv, 5),
                $category->revenue_share . '%',
                $category->active ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'
            ],
            'show' => ['url' => route('admin.categories.show', ['category' => $category->id], false)],
            'edit' => ['url' => route('admin.categories.edit', ['category' => $category->id], false)],
            'delete' => ['url' => route('admin.categories.destroy', ['category' => $category->id], false)],
            'extra' => [
                view('components.list.row-action', [
                    'click' => 'Ads.item.updateRow(this)',
                    'title' => $category->active ? 'Make Private' : 'Make Public',
                    'icon' => $category->active ? 'lock' : 'lock_open',
                    'url' => route('admin.categories.activate', ['category' => $category->id, 'active' => (int)!$category->active], false)
                ]),
                view('components.list.row-action', [
                    'click' => 'Ads.item.openExtra(this)',
                    'title' => "GEO Values",
                    'icon' => "travel_explore",
                    'url' => route('admin.categories.countries.index', ['category' => $category->id], false)
                ])->render()
            ]
        ]);
    }

    private function form($category = null) {
        return view('components.categories.form', isset($category) ? compact('category') : []);
    }

    private function searchForm() {
        return view('components.categories.search-form');
    }

    private function listHeader() {
        return view('components.list.header', ['title' => 'Available Categories', 'add' => $this->form(), 'search' => $this->searchForm()]);
    }

    public function show(Category $category) {
        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Category ID:', 'value' => $category->id],
                ['caption' => 'Title:', 'value' => $category->title],
                ['caption' => 'CPM:', 'value' => round($category->cpm, 5)],
                ['caption' => 'CPC:', 'value' => round($category->cpc, 5)],
                ['caption' => 'CPV:', 'value' => round($category->cpv, 5)],
                ['caption' => 'Revenue Share:', 'value' => $category->revenue_share . '%'],
                ['caption' => 'Public:', 'value' => $category->active ? '<a class="approved">✔</a>' : '<a class="declined">✘</a>'],
            ]
        ]);
    }

    public function edit(Category $category) {
        return $this->form($category);
    }

    public function destroy(Category $category) {
        return DB::transaction(function () use ($category) {
            // Move back balances to user
            InvoiceCampaign::query()
                ->join('campaigns', function ($q) use ($category) {
                    $q->whereColumn('campaigns.id', 'invoices_campaigns.campaign_id')
                        ->whereNull('campaigns.deleted_at')
                        ->where('campaigns.category_id', $category->id);
                })
                ->update(['invoices_campaigns.amount' => DB::raw('invoices_campaigns.current')]);

            // Unapprove domains
            PublisherDomain::query()
                ->where('category_id', $category->id)
                ->update(['approved_at' => null, 'approved_by_id' => null]);

            return $category->delete();
        });
    }

    private function validateFields(Request $request, array|null $fields = null): \Illuminate\Contracts\Validation\Validator|array {
        $allFields = [
            'title' => 'required|string',
            'cpm' => 'required|regex:/^\d*(\.\d+)?$/',
            'cpc' => 'required|regex:/^\d*(\.\d+)?$/',
            'cpv' => 'required|regex:/^\d*(\.\d+)?$/',
            'revenue_share' => 'required|between:0.00,100.00',
        ];
        if (isset($fields)) {
            $allFields = array_filter($allFields, fn($key) => in_array($key, $fields), ARRAY_FILTER_USE_KEY);
        }
        return Validator::make($request->all(), $allFields);
    }

    public function store(Request $request) {
        $validator = $this->validateFields($request);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        try {
            $category = Category::create([
                'title' => $request->title,
                'cpm' => $request->cpm,
                'cpc' => $request->cpc,
                'cpv' => $request->cpv,
                'revenue_share' => $request->revenue_share,
                'active' => 1,
            ]);
            return $this->success($this->listRow($category)->render());

        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Request $request, Category $category) {
        $validator = $this->validateFields($request);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        try {
            $request['active'] = $request->filled('active') ? 1 : 0;
            $category->update($request->all());
            return $this->success($this->listRow($category->fresh())->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }
}
