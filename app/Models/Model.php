<?php

namespace App\Models;

use App\Helpers\QueryBuilderHelper;

abstract class Model extends \Illuminate\Database\Eloquent\Model {
    public function newEloquentBuilder($query) {
        return new QueryBuilderHelper($query);
    }
}
