<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Models\User;
use Arr;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromosController extends Controller {

    public function __construct() {
        $this->middleware(function ($request, $next) {
            self::requirePermission(self::ANY);
            return $next($request);
        });
    }

    public function index(Request $request) {
        return view('layouts.app', [
            'page_title' => 'Manage Promos',
            'slot' => view('components.list.list', ['key' => 'all', 'header' => $this->listHeader(), 'body' => $this->list($request)])
        ]);
    }

    public function list(Request $request) {
        $promos = $this->search(Promo::query())->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('admin.promos.list', absolute: false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Title', 'Promo Code', 'Promo Bonus', 'Total Codes', 'Actions'],
            'rows' => $promos->getCollection()->toString(fn($promo) => $this->listRow($promo)->render()),
            'pagination' => $promos->links()
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = request();
            $this->whereString('promos.title', $req->title, $q);
            $this->whereString('promos.code', $req->code, $q);
        }
        return $q;
    }

    private function listRow(Promo $item) {
        $data = [
            'id' => $item->id,
            'columns' => [$item->title, $item->code, $item->bonus . '%', $item->purchased . ' of ' . ($item->total ?? 'Unlimited')],
            'show' => ['url' => route('admin.promos.show', ['promo' => $item->id], false)]
        ];
        if (self::checkPermission(self::UPDATE)) {
            $data['edit'] = ['url' => route('admin.promos.edit', ['promo' => $item->id], false)];
        }
        if (self::checkPermission(self::DELETE)) {
            $data['delete'] = ['url' => route('admin.promos.destroy', ['promo' => $item->id], false)];
        }
        return view('components.list.row', $data);
    }

    private function form(Promo $promo = null) {
        return view('components.promos.form', compact('promo'));
    }

    private function searchForm() {
        return view('components.promos.search-form');
    }

    private function listHeader() {
        $data = ['title' => 'Existing Promos', 'search' => $this->searchForm()];
        if (self::checkPermission(self::CREATE)) {
            $data['add'] = $this->form();
        }
        return view('components.list.header', $data);
    }

    public function show(Promo $promo) {
        return view('components.list.row-details', [
            'rows' => [
                ['caption' => 'Promo Code ID:', 'value' => $promo->id],
                ['caption' => 'Title:', 'value' => $promo->title],
                ['caption' => 'Promo Code:', 'value' => $promo->code],
				['caption' => 'Total Issued:', 'value' => $promo->total ?? 'Unlimited'],
                ['caption' => 'Already Used:', 'value' => $promo->purchased],
				['caption' => 'Promo Bonus:', 'value' => $promo->bonus . '%'],
            ]
        ]);
    }

    public function edit(Promo $promo) {
        self::requirePermission(self::UPDATE);
        return $this->form($promo);
    }

    public function destroy(Promo $promo) {
        self::requirePermission(self::DELETE);
        return $promo->delete();
    }

    private function validateFields(Request $request, array|null $fields = null): \Illuminate\Contracts\Validation\Validator|array {
        $allFields = [
            'title' => 'required|string',
            'code' => 'required|string',
            'total' => 'nullable|integer',
            'bonus' => 'required|between:0.00,100.00',
        ];
        if (isset($fields)) {
            $allFields = array_filter($allFields, fn($key) => in_array($key, $fields), ARRAY_FILTER_USE_KEY);
        }
        return Validator::make($request->all(), $allFields);
    }

    public function store(Request $request) {
        self::requirePermission(self::CREATE);

        $validator = $this->validateFields($request);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }
        try {
            $promo = Promo::create(['title' => $request->title, 'code' => $request->code, 'total' => $request->total, 'bonus' => $request->bonus]);
            return $this->success($this->listRow($promo->refresh())->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Request $request, Promo $promo) {
        self::requirePermission(self::UPDATE);

        $validator = $this->validateFields($request);
        if ($validator->fails()) {
            return $this->failure($validator->errors());
        }

        try {
            $promo->update($request->all());
            return $this->success($this->listRow($promo)->render());
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }

    ////////////////////////////////////////////////////
    private const ANY = 'Any';
    private const CREATE = 'Create';
    private const UPDATE = 'Update';
    private const DELETE = 'Delete';

    private static function checkPermission(string|array $permission): bool {
        return User::hasAnyPermissions('promos', $permission === self::ANY ? [self::CREATE, self::UPDATE, self::DELETE] : Arr::wrap($permission));
    }

    private static function requirePermission(string $permission) {
        if (!self::checkPermission($permission)) {
            abort(403);
        }
    }
}
