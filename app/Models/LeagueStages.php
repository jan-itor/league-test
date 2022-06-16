<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string name
 * @property string description
 * @property string finished_at
 * @property string created_at
 * @property string updated_at
 *
 * @property Fixtures[] fixtures
 *
 * @mixin Builder
 */
class LeagueStages extends Model
{
    use HasFactory;

    protected $table = 'league_stages';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fixtures()
    {
        return $this->hasMany('App\Models\Fixtures', 'stage_id');
    }
}
