<?php

namespace App\Models;

use App\Traits\HasUser;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\WithdrawalRequest
 *
 * @property int $id
 * @property int $user_id
 * @property string $currency
 * @property string $wallet
 * @property string|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invoice[] $invoices
 * @property-read int|null $invoices_count
 * @property-read \App\Models\User $user
 * @method static \App\Helpers\QueryBuilderHelper|WithdrawalRequest newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|WithdrawalRequest newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|WithdrawalRequest page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|WithdrawalRequest query()
 * @method static \App\Helpers\QueryBuilderHelper|WithdrawalRequest whereConfirmedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|WithdrawalRequest whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|WithdrawalRequest whereCurrency($value)
 * @method static \App\Helpers\QueryBuilderHelper|WithdrawalRequest whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|WithdrawalRequest whereUpdatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|WithdrawalRequest whereUserId($value)
 * @method static \App\Helpers\QueryBuilderHelper|WithdrawalRequest whereWallet($value)
 * @mixin \Eloquent
 */
class WithdrawalRequest extends Model {
    use HasUser;

    public $table = 'withdrawals_requests';
    public $fillable = ['user_id', 'currency', 'wallet', 'confirmed_at'];

    public function invoices(): Relations\HasMany {
        return $this->hasMany(Invoice::class);
    }

    public function isConfirmed(): bool {
        return $this->confirmed_at !== null;
    }

    public function isPaid(): bool {
        return !empty($this->invoices) && $this->invoices[0]->isPaid();
    }

    public function getPayment(): ?Payment {
        return !empty($this->invoices) ? $this->invoices[0]->payment : null;
    }
}