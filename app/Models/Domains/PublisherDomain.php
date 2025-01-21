<?php

namespace App\Models\Domains;

use App\Models\Category;
use App\Models\Place;
use App\Models\UserPublisher;
use Database\Factories\PublisherDomainFactory;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\Domains\PublisherDomain
 *
 * @property int $id
 * @property int $publisher_id
 * @property string $domain
 * @property int|null $category_id Set by managers.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $approved_by_id
 * @property-read \App\Models\UserManager|null $approvedBy
 * @property-read Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection|Place[] $places
 * @property-read int|null $places_count
 * @property-read UserPublisher $publisher
 * @method static \App\Helpers\QueryBuilderHelper|UserDomain approved()
 * @method static \Database\Factories\PublisherDomainFactory factory(...$parameters)
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain query()
 * @method static \App\Helpers\QueryBuilderHelper|UserDomain unapproved()
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain whereApprovedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain whereApprovedById($value)
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain whereCategoryId($value)
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain whereDomain($value)
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain wherePublisherId($value)
 * @method static \App\Helpers\QueryBuilderHelper|PublisherDomain whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PublisherDomain extends UserDomain {
    use HasFactory;

    public $fillable = ['publisher_id', 'domain', 'category_id', 'approved_by_id', 'approved_at'];
    protected $table = "users_publishers_domains";

    public function publisher(): Relations\BelongsTo {
        return $this->belongsTo(UserPublisher::class, 'publisher_id');
    }

    public function category(): Relations\BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function places(): Relations\HasMany {
        return $this->hasMany(Place::class, 'domain_id');
    }

    protected static function newFactory() {
        return PublisherDomainFactory::new();
    }

    public static function activePlacesCount() {
        return Place::select(DB::raw('COUNT(id)'))
            ->whereColumn('domain_id', '=', 'users_publishers_domains.id')
            ->whereNotNull('approved_at');
    }

    public static function inUse(PublisherDomain $domain) {
        return $domain->places()->whereNotNull('approved_at')->exists();
    }
}
