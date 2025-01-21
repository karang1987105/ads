<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * App\Models\EmailTemplate
 *
 * @property int $id
 * @property string $title
 * @property string $subject
 * @property string $message
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmailTemplateAttachment[] $attachments
 * @property-read int|null $attachments_count
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplate newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplate newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplate page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplate query()
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplate whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplate whereMessage($value)
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplate whereSubject($value)
 * @method static \App\Helpers\QueryBuilderHelper|EmailTemplate whereTitle($value)
 * @mixin \Eloquent
 */
class EmailTemplate extends Model {
    public $table = "emails_templates";
    public $timestamps = false;
    protected $fillable = ['id', 'title', 'subject', 'message'];
    public $with = ['attachments'];

    public function attachments(): HasMany {
        return $this->hasMany(EmailTemplateAttachment::class, "email_template_id");
    }
}
