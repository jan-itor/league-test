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
 * @property Fixture[] fixtures
 *
 * @mixin Builder
 */
class LeagueStage extends Model
{
    use HasFactory;

    protected $table = 'league_stages';

    /**
     * @return LeagueStage|array|Model|object|null
     */
    public function getLastPlayedStage()
    {
        return $this->whereNotNull(['finished_at'])->orderBy('id', 'DESC')->first();
    }

    /**
     * @return LeagueStage|array|Model|object|null
     */
    public function getNextStageToPlay()
    {
        return $this->whereNull('finished_at')->orderBy('id')->first();
    }

    /**
     * @return LeagueStage[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getStagesToPlay()
    {
        return $this->whereNull('finished_at')->get();
    }

    /**
     * @param LeagueStage $model
     * @return bool
     */
    public function processSave(self $model): bool
    {
        return $model->save();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fixtures()
    {
        return $this->hasMany('App\Models\Fixture', 'stage_id');
    }
}
