<?php

namespace App\Helpers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

class BlueprintHelper extends Blueprint {
    public function amount($column, bool $unsigned = true, bool $crypto = false): ColumnDefinition {
        $total = $crypto ? 16 : 10;
        $places = $crypto ? 8 : 4;
        return $this->decimal($column, $total, $places, $unsigned);
    }

    public function percent($column): ColumnDefinition {
        return $this->decimal($column, 5)->unsigned();
    }

    public function char($column, $length = null, bool $ascii = false): ColumnDefinition {
        $col = parent::char($column, $length);
        return $ascii ? self::ascii($col) : $col;
    }

    public function string($column, $length = null, bool $ascii = false): ColumnDefinition {
        $col = parent::string($column, $length);
        return $ascii ? self::ascii($col) : $col;
    }

    public function enum($column, array $allowed): ColumnDefinition {
        return self::ascii(parent::enum($column, $allowed));
    }

    public function set($column, array $allowed): ColumnDefinition {
        return self::ascii(parent::set($column, $allowed));
    }

    private static function ascii(ColumnDefinition $column): ColumnDefinition {
        return $column->charset('ascii')->collation('ascii_general_ci');
    }

    public function approvable($column = 'approved'): BlueprintHelper {
        $this->dateTime($column . '_at')->nullable();
        $this->unsignedBigInteger($column . '_by_id')->nullable()->index();
        $this->foreign($column . '_by_id')->on('users_managers')->references('user_id')->cascadeOnUpdate()->nullOnDelete();
        return $this;
    }
}
