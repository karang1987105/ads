<?php

namespace App\Models;

use App\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\Payment
 *
 * @property int $id
 * @property string|null $title
 * @property int $user_id The user who is selling/purchasing services.
 * @property string|null $currency_id NULL when currency is deleted.
 * @property string $amount USD. Positive means deposit, Negative means withdrawal.
 * @property string|null $exchange_rate Exchange rate of the amount to currency. It's NULL and will be set on confirming the payment
 * @property string|null $txid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property int|null $confirmed_by_id
 * @property-read \App\Models\UserManager|null $confirmedBy
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \App\Models\User $user
 * @method static \App\Helpers\QueryBuilderHelper|Payment newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Payment newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Payment page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|Payment query()
 * @method static \App\Helpers\QueryBuilderHelper|Payment whereAmount($value)
 * @method static \App\Helpers\QueryBuilderHelper|Payment whereConfirmedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Payment whereConfirmedById($value)
 * @method static \App\Helpers\QueryBuilderHelper|Payment whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Payment whereCurrencyId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Payment whereExchangeRate($value)
 * @method static \App\Helpers\QueryBuilderHelper|Payment whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Payment whereTitle($value)
 * @method static \App\Helpers\QueryBuilderHelper|Payment whereTxid($value)
 * @method static \App\Helpers\QueryBuilderHelper|Payment whereUpdatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Payment whereUserId($value)
 * @mixin \Eloquent
 */
class Payment extends Model {
    use HasFactory, HasUser;

    public $fillable = ['title', 'user_id', 'currency_id', 'amount', 'exchange_rate', 'confirmed_at', 'confirmed_by_id', 'txid'];
    public $casts = ['confirmed_at' => 'datetime'];

    public function currency(): Relations\BelongsTo {
        return $this->belongsTo(Currency::class);
    }

    public function confirmedBy(): Relations\BelongsTo {
        return $this->belongsTo(UserManager::class, 'confirmed_by_id', 'user_id');
    }

    public function invoices(): Relations\HasMany {
        return $this->hasMany(Invoice::class);
    }

    public function isConfirmed(): bool {
        return $this->confirmed_at !== null;
    }

    public function isWithdrawalRequest(): bool {
        return $this->invoices()->whereNotNull('withdrawal_request_id')->exists();
    }

    public function isArchived(): bool {
        return $this->invoices()->where('archived', true)->exists();
    }
}
