<?php

namespace App\Models\Domains;

use App\Models\AdBanner;
use App\Models\AdVideo;
use App\Models\Category;
use App\Models\Model;
use App\Models\Place;
use App\Models\UserManager;
use App\Rules\Domain;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property Category $category
 * @method static approved()
 * @method static unapproved()
 */
abstract class UserDomain extends Model {
    public $casts = ['approved_at' => 'datetime'];

    public function approvedBy(): Relations\BelongsTo {
        return $this->belongsTo(UserManager::class, 'approved_by_id', 'user_id');
    }

    public function isApproved(): bool {
        return $this->approved_at != null;
    }

    public function scopeApproved(Builder $builder): Builder {
        return $builder->whereNotNull('approved_at');
    }

    public function scopeUnapproved(Builder $builder): Builder {
        return $builder->whereNull('approved_at');
    }
}
