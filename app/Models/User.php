<?php

namespace App\Models;

use App\Helpers\QueryBuilderHelper;
use App\Models\Logs\LoginAttempt;
use App\Notifications\UserUpdate;
use Auth;
use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Notification;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $type
 * @property Carbon|null $active
 * @property int|null $active_by_id
 * @property string|null $country_id
 * @property string $company
 * @property string $phone
 * @property string $business_id
 * @property string $address
 * @property string $state
 * @property string $city
 * @property string $zip
 * @property DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read UserManager|null $activeBy
 * @property-read UserAdvertiser|null $advertiser
 * @property-read Country|null $country
 * @property-read UserManager|null $manager
 * @property-read int|null $notifications_count
 * @property-read UserPublisher|null $publisher
 * @property-read Collection<int, TicketThread> $tickets
 * @property-read int|null $tickets_count
 * @method static QueryBuilderHelper|User active()
 * @method static QueryBuilderHelper|User asAdmin()
 * @method static QueryBuilderHelper|User asAdvertiser()
 * @method static QueryBuilderHelper|User asManager()
 * @method static QueryBuilderHelper|User asNonAdmin()
 * @method static QueryBuilderHelper|User asNonManager()
 * @method static QueryBuilderHelper|User asPublisher()
 * @method static QueryBuilderHelper|User excludingMe()
 * @method static UserFactory factory(...$parameters)
 * @method static QueryBuilderHelper|User inactive()
 * @method static QueryBuilderHelper|User newModelQuery()
 * @method static QueryBuilderHelper|User newQuery()
 * @method static QueryBuilderHelper|User page($page = 1, $columns = [])
 * @method static QueryBuilderHelper|User query()
 * @method static QueryBuilderHelper|User whereActive($value)
 * @method static QueryBuilderHelper|User whereActiveById($value)
 * @method static QueryBuilderHelper|User whereAddress($value)
 * @method static QueryBuilderHelper|User whereBusinessId($value)
 * @method static QueryBuilderHelper|User whereCity($value)
 * @method static QueryBuilderHelper|User whereCompany($value)
 * @method static QueryBuilderHelper|User whereCountryId($value)
 * @method static QueryBuilderHelper|User whereCreatedAt($value)
 * @method static QueryBuilderHelper|User whereEmail($value)
 * @method static QueryBuilderHelper|User whereEmailVerifiedAt($value)
 * @method static QueryBuilderHelper|User whereId($value)
 * @method static QueryBuilderHelper|User whereName($value)
 * @method static QueryBuilderHelper|User whereNotifications($value)
 * @method static QueryBuilderHelper|User wherePassword($value)
 * @method static QueryBuilderHelper|User wherePhone($value)
 * @method static QueryBuilderHelper|User whereRememberToken($value)
 * @method static QueryBuilderHelper|User whereState($value)
 * @method static QueryBuilderHelper|User whereType($value)
 * @method static QueryBuilderHelper|User whereUpdatedAt($value)
 * @method static QueryBuilderHelper|User whereZip($value)
 * @mixin Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'country_id',
        'company',
        'phone',
        'business_id',
        'address',
        'state',
        'city',
        'zip',
        'notifications',
        'active',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'datetime',
    ];

    public function getNotificationsAttribute(): array {
        return explode(',', $this->attributes['notifications']);
    }

    public function setNotificationsAttribute(array $values) {
        $this->attributes['notifications'] = implode(',', $values);
    }

    public function isAdmin(): bool {
        return $this->isManager() && $this->manager->is_admin;
    }

    public function isManager(): bool {
        return $this->type === 'Manager';
    }

    public function isAdvertiser(): bool {
        return $this->type === 'Advertiser';
    }

    public function isPublisher(): bool {
        return $this->type === 'Publisher';
    }

    public static function admin(): User {
        return self::asAdmin()->limit(1)->first();
    }

    public function manager(): Relations\HasOne {
        return $this->hasOne(UserManager::class);
    }

    public function advertiser(): Relations\HasOne {
        return $this->hasOne(UserAdvertiser::class);
    }

    public function publisher(): Relations\HasOne {
        return $this->hasOne(UserPublisher::class);
    }

    public function country(): Relations\BelongsTo {
        return $this->belongsTo(Country::class);
    }

    public function tickets(): Relations\HasManyThrough {
        return $this->hasManyThrough(TicketThread::class, TicketMessage::class, 'user_id', 'id', 'id', 'thread_id')
            ->where('tickets_messages.reply', '=', false);
    }

    public function invoices(bool|null $paid = null): Relations\HasMany {
        $hasMany = $this->hasMany(Invoice::class);
        return isset($paid) ? ($paid ? $hasMany->paid() : $hasMany->notPaid()) : $hasMany;
    }

    public function lastLoginAttempts(): Relations\HasMany {
        return $this->hasMany(LoginAttempt::class, 'email', 'email')
            ->latest();
    }

    public function activeBy(): BelongsTo {
        return $this->belongsTo(UserManager::class, 'active_by_id', 'user_id');
    }

    /*********************************** Scopes ***********************************/
    public function scopeAsAdmin(Builder $query): Builder {
        return self::asManager()->join('users_managers', function (JoinClause $join) {
            $join->on('users_managers.user_id', '=', 'users.id')
                ->where('users_managers.is_admin', '=', true);
        });
    }

    public function scopeAsNonAdmin(Builder $query): Builder {
        return self::asManager()->join('users_managers', function (JoinClause $join) {
            $join->on('users_managers.user_id', '=', 'users.id')
                ->whereNull('users_managers.is_admin');
        });
    }

    public function scopeAsManager(Builder $query): Builder {
        return $query->where('users.type', '=', 'Manager')->with('manager');
    }

    public function scopeAsAdvertiser(Builder $query): Builder {
        return $query->where('users.type', '=', 'Advertiser')->with('advertiser');
    }

    public function scopeAsPublisher(Builder $query): Builder {
        return $query->where('users.type', '=', 'Publisher')->with('publisher');
    }

    public function scopeAsNonManager(Builder $query): Builder {
        return $query->where('users.type', '!=', 'Manager')->with('publisher');
    }

    public function scopeActive(Builder $query): Builder {
        return $query->whereNotNull('users.active');
    }

    public function scopeInactive(Builder $query): Builder {
        return $query->whereNull('users.active');
    }

    public function scopeExcludingMe(Builder $query): Builder {
        return $query->where('users.id', '!=', Auth::id());
    }

    public function notifyUser($type, $data = []) {
        if (!empty($this->notifications)) {
            $send = (in_array($type, [UserUpdate::$TYPE_ACCOUNT_SUSPENDED, UserUpdate::$TYPE_ACCOUNT_ACTIVATED]) && in_array('Account', $this->notifications))
                || (in_array($type, [UserUpdate::$TYPE_DOMAIN_APPROVED, UserUpdate::$TYPE_DOMAIN_DECLINED]) && in_array('Domain', $this->notifications))
                || (in_array($type, [UserUpdate::$TYPE_PLACE_APPROVED, UserUpdate::$TYPE_PLACE_DECLINED]) && in_array('Place', $this->notifications))
                || (in_array($type, [UserUpdate::$TYPE_AD_APPROVED, UserUpdate::$TYPE_AD_DECLINED]) && in_array('Advertisement', $this->notifications))
                || (($type == UserUpdate::$TYPE_CAMPAIGN_EXPIRED) && in_array('Campaign', $this->notifications))
                || (in_array($type, [UserUpdate::$TYPE_WITHDRAWAL_CONFIRMATION, UserUpdate::$TYPE_WITHDRAWAL_CONFIRMED, UserUpdate::$TYPE_WITHDRAWAL_PAID]));

            if ($send) {
                Notification::send($this, new UserUpdate($type, $data));
            }
        }
    }

    public function newEloquentBuilder($query) {
        return new QueryBuilderHelper($query);
    }

    public static function isActive(): bool {
        return auth()->user()->active !== null;
    }

    public static function hasPermission(string $subject, string|null $permission = null): bool {
        $user = Auth::user();
        return $user->isAdmin() || ($user->isManager() && $user->manager->hasPermission($subject, $permission));
    }

    public static function hasAnyPermissions(string $subject, array $permissions): bool {
        $user = Auth::user();
        return $user->isAdmin() || ($user->isManager() && $user->manager->hasAnyPermissions($subject, $permissions));
    }

    public static function hasAllPermissions(string $subject, array $permissions): bool {
        $user = Auth::user();
        return $user->isAdmin() || ($user->isManager() && $user->manager->hasAllPermissions($subject, $permissions));
    }
}
