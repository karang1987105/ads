<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations;

trait HasUser {
    public function user(): Relations\BelongsTo {
        return $this->belongsTo(User::class);
    }
}
