<?php

namespace App\Models;

use App\Traits\HasUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;

/**
 * \App\Models\Invoice
 *
 * @property int $id
 * @property string $title
 * @property int $user_id
 * @property string $amount
 * @property int|null $payment_id Reference to payment if paid
 * @property int $archived
 * @property int|null $promo
 * @property int $bonus
 * @property int|null $withdrawal_request_id Available for publishers invoices
 * @property int|null $created_by_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\InvoiceCampaign[] $campaigns
 * @property-read int|null $campaigns_count
 * @property-read \App\Models\UserManager|null $createdBy
 * @property mixed $balance
 * @property-read \App\Models\Payment|null $payment
 * @property-read \App\Models\InvoicePlace|null $place
 * @property-read \App\Models\User $user
 * @property-read \App\Models\WithdrawalRequest|null $withdrawalRequest
 * @method static \App\Helpers\QueryBuilderHelper|Invoice archived()
 * @method static \App\Helpers\QueryBuilderHelper|Invoice asWithdrawalRequest()
 * @method static \App\Helpers\QueryBuilderHelper|Invoice forWithdrawalRequest()
 * @method static \App\Helpers\QueryBuilderHelper|Invoice newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Invoice newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|Invoice notPaid()
 * @method static \App\Helpers\QueryBuilderHelper|Invoice ofAdvertisers($userId = null)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice ofPublishers($userId = null)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|Invoice paid()
 * @method static \App\Helpers\QueryBuilderHelper|Invoice query()
 * @method static \App\Helpers\QueryBuilderHelper|Invoice whereAmount($value)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice whereArchived($value)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice whereBonus($value)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice whereCreatedById($value)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice wherePaymentId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice wherePromo($value)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice whereTitle($value)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice whereUpdatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice whereUserId($value)
 * @method static \App\Helpers\QueryBuilderHelper|Invoice whereWithdrawalRequestId($value)
 * @mixin \Eloquent
 */
class Invoice extends Model {
    use HasUser;

    public $fillable = ['title', 'user_id', 'amount', 'payment_id', 'archived', 'withdrawal_request_id', 'bonus', 'promo', 'created_by_id'];
    public $casts = ['archive' => 'boolean', 'created_at' => 'datetime'];
    public $with = ['user:id,type,name'];

    public ?float $_balance = null;

    public function payment(): Relations\BelongsTo {
        return $this->belongsTo(Payment::class);
    }

    public function user(): Relations\BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function campaigns(): Relations\HasMany {
        return $this->hasMany(InvoiceCampaign::class);
    }

    public function place(): Relations\HasOne {
        return $this->hasOne(InvoicePlace::class);
    }

    public function withdrawalRequest(): Relations\BelongsTo {
        return $this->belongsTo(WithdrawalRequest::class);
    }

    public function createdBy(): Relations\BelongsTo {
        return $this->belongsTo(UserManager::class, 'created_by_id', 'user_id');
    }

    public function ofAdvertiser() {
        return $this->user->isAdvertiser();
    }

    public function ofPublisher() {
        return $this->user->isPublisher();
    }

    public function isPaid(): bool {
        return $this->payment_id !== null && $this->payment->confirmed_at !== null;
    }

    public function getBalance($fresh = false): float {
        if (!isset($this->_balance) || $fresh) {
            $this->_balance = $this->amount - $this->campaigns->sum('amount');
        }
        return $this->_balance;
    }

    public function getBalanceOfCampaign(Campaign|int $byCampaign): float {
        $campaignId = $byCampaign instanceof Campaign ? $byCampaign->id : $byCampaign;

        return $this->amount - $this->campaigns()->where('campaign_id', $campaignId)->sum('amount');
    }

    public function isWithdrawalRequest(): bool {
        return $this->withdrawal_request_id !== null;
    }

    public function scopeAsWithdrawalRequest(Builder $builder) {
        return $builder->whereNotNull('invoices.withdrawal_request_id');
    }

    public function scopeForWithdrawalRequest(Builder $builder) {
        return $builder->whereNull('invoices.withdrawal_request_id');
    }

    public function scopeArchived(Builder $builder) {
        $builder->where('archived', '=', true);
    }

    public function scopeOfAdvertisers(Builder $builder, $userId = null) {
        $builder->join('users_advertisers', function ($q) use ($userId) {
            $q->whereColumn('users_advertisers.user_id', '=', 'invoices.user_id');
            if (isset($userId)) {
                $q->where('invoices.user_id', '=', $userId);
            }
        });
    }

    public function scopeOfPublishers(Builder $builder, $userId = null) {
        $builder->join('users_publishers', function ($q) use ($userId) {
            $q->whereColumn('users_publishers.user_id', '=', 'invoices.user_id');
            if (isset($userId)) {
                $q->where('invoices.user_id', '=', $userId);
            }
        });
    }

    public function scopePaid(Builder $builder) {
        $builder->join("payments", fn($j) => $j->whereColumn('payments.id', '=', 'invoices.payment_id')->whereNotNull('payments.confirmed_at'));
    }

    public function scopeNotPaid(Builder $builder) {
        $builder->leftjoin("payments", 'payments.id', '=', 'invoices.payment_id');
        $builder->whereNull('payments.confirmed_at');
    }

    public function getBalanceAttribute(): float {
        return $this->getBalance();
    }

    public function setBalanceAttribute($value): void {
        $this->_balance = $value;
    }

    public static function getInstance(int $userId, float $amount, string $title, $attributes = []) {
        return new Invoice(array_merge(['title' => $title, 'user_id' => $userId, 'amount' => $amount], $attributes));
    }
}
