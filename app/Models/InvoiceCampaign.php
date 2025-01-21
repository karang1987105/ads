<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\InvoiceCampaign
 *
 * @property int $id
 * @property int $invoice_id
 * @property int $campaign_id
 * @property string $amount
 * @property string $current
 * @property-read \App\Models\Campaign $campaign
 * @property-read \App\Models\Invoice $invoice
 * @method static \App\Helpers\QueryBuilderHelper|InvoiceCampaign newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|InvoiceCampaign newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|InvoiceCampaign page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|InvoiceCampaign query()
 * @method static \App\Helpers\QueryBuilderHelper|InvoiceCampaign unbalanced()
 * @method static \App\Helpers\QueryBuilderHelper|InvoiceCampaign whereAmount($value)
 * @method static \App\Helpers\QueryBuilderHelper|InvoiceCampaign whereCampaignId($value)
 * @method static \App\Helpers\QueryBuilderHelper|InvoiceCampaign whereCurrent($value)
 * @method static \App\Helpers\QueryBuilderHelper|InvoiceCampaign whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|InvoiceCampaign whereInvoiceId($value)
 * @mixin \Eloquent
 */
class InvoiceCampaign extends Model {

    use SoftDeletes;

    protected $table = 'invoices_campaigns';
    public $incrementing = false;
    public $timestamps = false;
    public $fillable = ['invoice_id', 'campaign_id', 'current', 'amount'];

    public function invoice(): Relations\BelongsTo {
        return $this->belongsTo(Invoice::class);
    }

    public function campaign(): Relations\BelongsTo {
        return $this->belongsTo(Campaign::class);
    }

    public function isBalanced(): bool {
        return $this->getBalance() === 0.0;
    }

    public function isUnbalanced(): bool {
        return $this->getBalance() > 0;
    }

    public function getBalance(): float {
        return round($this->amount - $this->current, 9);
    }

    public function scopeUnbalanced($builder) {
        return $builder->whereColumn('invoices_campaigns.amount', '>', 'invoices_campaigns.current');
    }
}
