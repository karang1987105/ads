<?php

namespace App\Models;

use App\Traits\HasUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\UserManager
 *
 * @property int $user_id
 * @property int|null $is_admin
 * @property array $publishers
 * @property array $advertisers
 * @property array $advertisements
 * @property array $promos
 * @property array $send_email
 * @property-read \App\Models\User $user
 * @method static \App\Helpers\QueryBuilderHelper|UserManager active()
 * @method static \App\Helpers\QueryBuilderHelper|UserManager adminExcluded()
 * @method static \App\Helpers\QueryBuilderHelper|UserManager asAdmin()
 * @method static \Database\Factories\UserManagerFactory factory(...$parameters)
 * @method static \App\Helpers\QueryBuilderHelper|UserManager inactive()
 * @method static \App\Helpers\QueryBuilderHelper|UserManager newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|UserManager newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|UserManager page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|UserManager query()
 * @method static \App\Helpers\QueryBuilderHelper|UserManager whereAdvertisements($value)
 * @method static \App\Helpers\QueryBuilderHelper|UserManager whereAdvertisers($value)
 * @method static \App\Helpers\QueryBuilderHelper|UserManager whereIsAdmin($value)
 * @method static \App\Helpers\QueryBuilderHelper|UserManager wherePromos($value)
 * @method static \App\Helpers\QueryBuilderHelper|UserManager wherePublishers($value)
 * @method static \App\Helpers\QueryBuilderHelper|UserManager whereSendEmail($value)
 * @method static \App\Helpers\QueryBuilderHelper|UserManager whereUserId($value)
 * @mixin \Eloquent
 */
class UserManager extends Model {
    use hasFactory, HasUser;

    protected $table = 'users_managers';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = false;
    public $with = ['user'];
    public $fillable = [
        'publishers',
        'advertisers',
        'advertisements',
        'promos',
        'send_email'
    ];

    public const PERMISSIONS = [
        'publishers' => [
            'List',
            'Create',
            'Update',
            'Delete',
            'Block',
            'Activate',
            'Send Email',
            'Add Fund',
            'Remove Fund',
            'Login Behalf',
            'Domains',
            'Places',
            'Withdrawal Requests'
        ],
        'advertisers' => [
            'List',
            'Create',
            'Update',
            'Delete',
            'Block',
            'Activate',
            'Send Email',
            'Add Fund',
            'Remove Fund',
            'Login Behalf',
            'Domains'
        ],
        'advertisements' => [
            'Create',
            'Update',
            'Delete',
            'Block',
            'Activate'
        ],
        'promos' => [
            'Create',
            'Update',
            'Delete'
        ],
        'send_email' => [
            'Create',
            'Update',
            'Delete',
            'Send'
        ]
    ];

    public function hasAnyPermissions($subject, array|null $permissions = null): bool {
        return in_array($subject, array_keys(self::PERMISSIONS))
            && !empty(array_intersect($this->$subject, $permissions));
    }

    public function hasAllPermissions($subject, array $permissions): bool {
        return in_array($subject, array_keys(self::PERMISSIONS))
            && count($permissions) === count(array_intersect($this->$subject, $permissions));
    }

    public function hasPermission($subject, string|null $permission = null): bool {
        return in_array($subject, array_keys(self::PERMISSIONS)) && in_array($permission, $this->$subject);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array List of publishers permissions
     */
    public function getPublishersAttribute(): array {
        return self::_explode($this->attributes['publishers']);
    }

    /**
     * @param array $values Comma separated list of publishers permissions
     */
    public function setPublishersAttribute(array $values) {
        $this->attributes['publishers'] = self::_implode($values);
    }

    /**
     * @return array List of advertisers permissions
     */
    public function getAdvertisersAttribute(): array {
        return self::_explode($this->attributes['advertisers']);
    }

    /**
     * @param array $values Comma separated list of advertisers permissions
     */
    public function setAdvertisersAttribute(array $values) {
        $this->attributes['advertisers'] = self::_implode($values);
    }

    /**
     * @return array List of advertisements permissions
     */
    public function getAdvertisementsAttribute(): array {
        return self::_explode($this->attributes['advertisements']);
    }

    /**
     * @param array $values Comma separated list of advertisements permissions
     */
    public function setAdvertisementsAttribute(array $values) {
        $this->attributes['advertisements'] = self::_implode($values);
    }

    /**
     * @return array List of promos permissions
     */
    public function getPromosAttribute(): array {
        return self::_explode($this->attributes['promos']);
    }

    /**
     * @param array $values Comma separated list of promos permissions
     */
    public function setPromosAttribute(array $values) {
        $this->attributes['promos'] = self::_implode($values);
    }

    /**
     * @return array List of promos permissions
     */
    public function getSendEmailAttribute(): array {
        return self::_explode($this->attributes['send_email']);
    }

    /**
     * @param array $values Comma separated list of promos permissions
     */
    public function setSendEmailAttribute(array $values) {
        $this->attributes['send_email'] = self::_implode($values);
    }

    private static function _explode($str): array {
        return explode(',', $str);
    }

    private static function _implode($arr): ?string {
        return $arr && is_array($arr) ? implode(',', $arr) : null;
    }

    public function scopeAdminExcluded(Builder $query): Builder {
        return $query->whereNull('is_admin');
    }

    public function scopeAsAdmin(Builder $query): Builder {
        return $query->where('is_admin', '=', true);
    }

    public function scopeActive(Builder $query): Builder {
        return $query->join('users', fn($join) => $join->on('users.id', '=', 'users_managers.user_id')->whereNotNull('users.active'));
    }

    public function scopeInactive(Builder $query): Builder {
        return $query->join('users', fn($join) => $join->on('users.id', '=', 'users_managers.user_id')->whereNull('users.active'));
    }
}
