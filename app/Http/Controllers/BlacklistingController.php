<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\BlockItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BlacklistingController extends Controller {
    public function index(Request $request) {
        return view('layouts.app', [
            'page_title' => 'Manage Blacklisting',
            'slot' => view('components.list.list', [
                'key' => 'all',
                'header' => $this->listHeader(),
                'body' => $this->list($request)
            ])
        ]);
    }

    private function listHeader() {
        return view('components.list.header', [
            'title' => 'Blacklisted Domains',
            'add' => $this->form(),
            'search' => $this->searchForm()
        ]);
    }

    public function list(Request $request) {
        $items = $this->search(BlockItem::query())->page($request->query->get('page'));
        return view('components.list.body', [
            'url' => route('admin.blacklisting.list', absolute: false),
            'query' => json_encode($request->all(), JSON_FORCE_OBJECT),
            'header' => ['Domain', 'Actions'],
            'rows' => $items->getCollection()->toString(fn($c) => $this->listRow($c)->render()),
            'pagination' => $items->links()
        ]);
    }

    private function search(Builder $q): Builder {
        if ($this->isSearch()) {
            $req = request();
            $this->whereString('domain', $req->domain, $q);
        }
        return $q;
    }

    private function listRow(BlockItem $item) {
        return view('components.list.row', [
            'id' => $item->id,
            'columns' => [$item->domain],
            'edit' => ['url' => route('admin.blacklisting.edit', $item->id, false)],
            'delete' => ['url' => route('admin.blacklisting.destroy', $item->id, false)]
        ]);
    }

    private function form(BlockItem $item = null) {
        return view('components.blacklisting.form', isset($item) ? compact('item') : []);
    }

    private function searchForm() {
        return view('components.blacklisting.search-form');
    }

    public function edit(BlockItem $blacklisting) {
        return $this->form($blacklisting);
    }

    public function destroy(BlockItem $blacklisting) {
        return $blacklisting->delete();
    }

    public function store(Request $request) {
        $domain = Helper::getUrlDomain($request->get('domain'));

        if (!$domain) {
            return $this->failure(['domain' => 'Domain value is required.']);
        }

        if (BlockItem::where('domain', $domain)->exists()) {
            return $this->failure(['domain' => 'Domain is already exists.']);
        }

        try {
            $item = BlockItem::create(['domain' => $domain]);
            return $this->success($this->listRow($item)->render());

        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    public function update(Request $request, BlockItem $blacklisting) {
        $domain = Helper::getUrlDomain($request->get('domain'));

        if (!$domain) {
            return $this->failure(['domain' => 'Domain value is required.']);
        }

        if ($blacklisting->domain !== $domain && BlockItem::where('domain', $domain)->exists()) {
            return $this->failure(['domain' => 'Domain is already exists.']);
        }

        try {
            $blacklisting->update(['domain' => $domain]);
            return $this->success($this->listRow($blacklisting->fresh())->render());
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}