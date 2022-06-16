<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string name
 * @property string created_at
 * @property string updated_at
 *
 * @property Stats stats
 *
 * @mixin Builder
 */
class Teams extends Model
{
    use HasFactory;

    protected $table = 'teams';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function homeTeams()
    {
        return $this->hasMany('App\Models\Fixtures', 'home_team_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function awayTeams()
    {
        return $this->hasMany('App\Models\Fixtures', 'away_team_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function stats()
    {
        return $this->hasOne('App\Models\Stats', 'team_id');
    }
}
