<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations;

/**
 * App\Models\InvoicePlace
 *
 * @property int $invoice_id
 * @property int $campaign_id
 * @property int $place_id
 * @property-read \App\Models\Campaign $campaign
 * @property-read \App\Models\Invoice $invoice
 * @property-read \App\Models\Place $place
 * @method static \App\Helpers\QueryBuilderHelper|InvoicePlace newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|InvoicePlace newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|InvoicePlace page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|InvoicePlace query()
 * @method static \App\Helpers\QueryBuilderHelper|InvoicePlace whereCampaignId($value)
 * @method static \App\Helpers\QueryBuilderHelper|InvoicePlace whereInvoiceId($value)
 * @method static \App\Helpers\QueryBuilderHelper|InvoicePlace wherePlaceId($value)
 * @mixin \Eloquent
 */
class InvoicePlace extends Model {
    protected $table = 'invoices_places';
    protected $primaryKey = 'invoice_id';
    public $incrementing = false;
    public $timestamps = false;
    public $fillable = ['invoice_id', 'campaign_id', 'place_id'];

    public function invoice(): Relations\BelongsTo {
        return $this->belongsTo(Invoice::class);
    }

    public function campaign(): Relations\BelongsTo {
        return $this->belongsTo(Campaign::class);
    }

    public function place(): Relations\BelongsTo {
        return $this->belongsTo(Place::class);
    }
}
