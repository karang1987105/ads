<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\EmailTemplateAttachment
 *
 * @property int $id
 * @property int $email_template_id
 * @property string $name
 * @property string $attachment
 * @property bool $inline
 * @property-read \App\Models\EmailTemplate $emailTemplate
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplateAttachment inline()
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplateAttachment newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplateAttachment newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplateAttachment page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplateAttachment query()
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplateAttachment whereAttachment($value)
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplateAttachment whereEmailTemplateId($value)
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplateAttachment whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplateAttachment whereInline($value)
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplateAttachment whereName($value)
 * @mixin \Eloquent
 */
class EmailTemplateAttachment extends Model {
    public $table = "emails_templates_attachments";
    public $timestamps = false;
    protected $fillable = ['id', 'email_template_id', 'name', 'attachment', 'inline'];
    protected $casts = ['inline' => 'boolean'];

    public function emailTemplate(): BelongsTo {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function scopeInline(Builder $builder): Builder {
        return $builder->where('inline', '=', true);
    }
}
