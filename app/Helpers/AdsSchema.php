<?php

namespace App\Helpers;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdsSchema extends Schema {
    public static function create(string $table, Closure $callback) {
        $schema = DB::connection()->getSchemaBuilder();
        $schema->blueprintResolver(fn($table, $callback) => new BlueprintHelper($table, $callback));
        $schema->disableForeignKeyConstraints();
        $schema->create($table, $callback);
        $schema->enableForeignKeyConstraints();
    }

    public static function dropDatabaseIfExists(string $name) {
        parent::disableForeignKeyConstraints();
        parent::dropDatabaseIfExists($name);
        parent::enableForeignKeyConstraints();
    }
}