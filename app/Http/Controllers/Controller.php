<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\MessageBag;

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function json($result, $errors, $statusCode, $alert = null): JsonResponse {
        return response()->json(compact('result', 'errors', 'alert'), $statusCode);
    }

    protected function success($result, $alert = null): JsonResponse {
        return $this->json($result, null, 200, $alert);
    }

    protected function failure($errors): JsonResponse {
        $errors = $errors instanceof MessageBag ? $errors->toArray() : $errors;
        foreach ($errors as $k => $v) {
            $pos = strpos($k, '.');
            if ($pos !== false) {
                $kk = substr($k, 0, $pos) . '[' . substr($k, $pos + 1) . ']';
                $kk = str_replace('.', '][', $kk);
                $errors[$kk] = $v;
                unset($errors[$k]);
            }
        }
        return $this->json(null, $errors, 422);
    }

    protected function exception($exception): JsonResponse {
        return $this->json(null, ['form' => 'Request failed.' . (config('app.debug') ? ' ' . $exception->getMessage() : '')], 500);
    }

    protected function whereString(string $key, string|null $value, EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder {
        return isset($value) ? $query->where($key, 'like', '%' . str_replace(' ', '%', trim($value)) . '%') : $query;
    }

    protected function whereNull(string $key, string|null $value, EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder {
        return isset($value) ? (!!$value ? $query->whereNull($key) : $query->whereNotNull($key)) : $query;
    }

    protected function whereNotNull(string $key, string|null $value, EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder {
        return isset($value) ? (!!$value ? $query->whereNotNull($key) : $query->whereNull($key)) : $query;
    }

    protected function whereEquals(string $key, string|null $value, EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder {
        return isset($value) ? $query->where($key, '=', $value) : $query;
    }

    protected function whereGt(string $key, string|null $value, EloquentBuilder|QueryBuilder $query, bool $inclusive = true): EloquentBuilder|QueryBuilder {
        return isset($value) ? $query->where($key, ($inclusive ? '>=' : '>'), $value) : $query;
    }

    protected function whereLt(string $key, string|null $value, EloquentBuilder|QueryBuilder $query, bool $inclusive = true): EloquentBuilder|QueryBuilder {
        return isset($value) ? $query->where($key, ($inclusive ? '<=' : '<'), $value) : $query;
    }

    protected function whereBoolean(string $key, bool|null $value, EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder {
        return isset($value) ? $query->where($key, '=', !!$value) : $query;
    }

    protected function isSearch(Request $request = null): bool {
        return ($request ?? request())->has('__search');
    }
}
