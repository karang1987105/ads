<?php

namespace App\Helpers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class QueryBuilderHelper extends Builder {
    /**
     * Flag to call FOUND_ROWS() after first query.
     * It will be set on paginate() and read in get()
     */
    private bool $withFoundRow = false;
    /**
     * Property to hold FOUND_ROWS() result.
     * It will be set on get() as FOUND_ROWS() result and read in getCountForPagination()
     */
    private int $totalRows = 0;

    /**
     * Instantiate custom paginator class.
     */
    protected function paginator($items, $total, $perPage, $currentPage, $options) {
        return new PaginatorHelper($items, $total, $perPage, $currentPage, $options);
    }

    public function page($page = 1, $columns = ['*']): LengthAwarePaginator {
        return $this->paginate(config('ads.default_results_per_page'), $columns, page: $page);
    }

    /**
     * Set $withFoundRow flag, adds SQL_CALC_FOUND_ROWS to the first column of the query and call parent.
     */
//    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
//        $this->query->withFoundRow = true;
//        $columns[0] = $this->raw('SQL_CALC_FOUND_ROWS ' . $columns[0]);
//
//        return parent::paginate($perPage, $columns, $pageName, $page);
//    }

    /**
     * It's get() with block injected after first query to check $withFoundRow and run FOUND_ROWS() and store it in $totalRows.
     */
//    public function get($columns = ['*']) {
//        $builder = $this->applyScopes();
//
//        // If we actually found models we will also eager load any relationships that
//        // have been specified as needing to be eager loaded, which will solve the
//        // n+1 query issue for the developers to avoid running a lot of queries.
//        $models = $builder->getModels($columns);
//
//        // Line added
//        if ($this->query instanceof QBuilderHelper && $this->query->withFoundRow) {
//            $this->query->totalRows = DB::select("SELECT FOUND_ROWS() AS total;")[0]->total;
//            $this->query->withFoundRow = false;
//        }
//
//        if (count($models) > 0) {
//            $models = $builder->eagerLoadRelations($models);
//        }
//
//        return $builder->getModel()->newCollection($models);
//    }
}
