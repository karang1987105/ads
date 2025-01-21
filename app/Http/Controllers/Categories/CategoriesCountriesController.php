<?php


namespace App\Http\Controllers\Categories;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryCountry;
use App\Models\Country;
use DB;
use Exception;
use Illuminate\Http\Request;

class CategoriesCountriesController extends Controller {
    public function index(Category $category) {
        return $this->list($category);
    }

    private function list(Category $category) {
        $countries = Country::visible()->where('category', '!=', 'Tier 4')->get()->groupBy('category')->sortKeys();
        $current = $category->countries->reduce(function ($arr, $country) {
            if ($country->category !== 'Tier 4') {
                $arr[$country->country_id] = ['cpc' => $country->cpc, 'cpm' => $country->cpm, 'cpv' => $country->cpv];
            }
            return $arr;
        }, []);

        $tiers = [];
        foreach ($countries as $tier => $countryList) {
            $tiers[$tier] = [];
            foreach ($countryList as $country) {
                $tiers[$tier][] = ['id' => $country->id, 'name' => $country->name] + ($current[$country->id] ?? []);
            }
        }

        $tier4 = $category->countries->filter(fn($c) => $c->country->category === 'Tier 4');
        return view('components.list.list', [
            'key' => 'all',
            'nobody' => true,
            'header' => view('components.list.header', [
                'title' => 'GEO Values',
                'refresh' => false,
                'slot' => view('components.categories.categories-countries-form', [
                    'category' => $category->id,
                    'tiers' => $tiers,
                    'tier4' => ['cpc' => $tier4->avg('cpc'), 'cpm' => $tier4->avg('cpm'), 'cpv' => $tier4->avg('cpv')]
                ])
            ])
        ]);
    }

    public function update(Category $category, Request $request) {
        $prices = $request->input('price');
        $insert = Country::visible()->get()->groupBy('category')->sortKeys()->reduce(function ($carry, $countries, $tier) use ($prices) {
            return $countries->reduce(function ($carry, $country) use ($prices, $tier) {
                $id = $country->id;
                $cpc = $prices[$tier]['all']['cpc'] ?? $prices[$tier][$id]['cpc'] ?? null;
                $cpm = $prices[$tier]['all']['cpm'] ?? $prices[$tier][$id]['cpm'] ?? null;
                $cpv = $prices[$tier]['all']['cpv'] ?? $prices[$tier][$id]['cpv'] ?? null;
                if (isset($cpc) || isset($cpm) || isset($cpv)) {
                    $carry[] = new CategoryCountry(['country_id' => $id, 'cpc' => $cpc, 'cpm' => $cpm, 'cpv' => $cpv]);
                }
                return $carry;
            }, $carry);
        }, []);

        try {
            DB::transaction(function () use ($category, $insert) {
                $category->countries()->delete();
                $category->countries()->saveMany($insert);
            });
            return $this->success(true);
        } catch (Exception $e) {
            return $this->exception($e);
        }
    }
}
