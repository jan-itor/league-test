<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property integer home_team_id
 * @property integer away_team_id
 * @property integer stage_id
 * @property integer home_team_score
 * @property integer away_team_score
 * @property integer played_at
 * @property string created_at
 * @property string updated_at
 *
 * @property Teams homeTeam
 * @property Teams awayTeam
 * @property LeagueStages stage
 *
 * @mixin Builder
 */
class Fixtures extends Model
{
    use HasFactory;

    const FIXTURE_DELIMITER = '-';
    const POSSIBLE_MAX_SCORE = 4;
    const POSSIBLE_MIN_SCORE = 0;
    const POSSIBLE_MIN_WIN_SCORE = 1;

    const POINTS_FOR_WIN = 3;
    const POINTS_FOR_DRAW = 1;

    const POWER_AFTER_WIN = 1;
    const POWER_AFTER_LOSE = 0.5;

    protected $table = 'fixtures';
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function homeTeam()
    {
        return $this->belongsTo('App\Models\Teams', 'home_team_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function awayTeam()
    {
        return $this->belongsTo('App\Models\Teams', 'away_team_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stage()
    {
        return $this->belongsTo('App\Models\LeagueStages', 'stage_id');
    }

}
