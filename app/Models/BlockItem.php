<?php

namespace App\Models;

/**
 * App\Models\BlockItem
 *
 * @property int $id
 * @property string $domain
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \App\Helpers\QueryBuilderHelper|BlockItem newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|BlockItem newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|BlockItem page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|BlockItem query()
 * @method static \App\Helpers\QueryBuilderHelper|BlockItem whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|BlockItem whereDomain($value)
 * @method static \App\Helpers\QueryBuilderHelper|BlockItem whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|BlockItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BlockItem extends Model {
    public $table = 'blacklisting';
    public $fillable = ['domain'];
}
