<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property integer team_id
 * @property integer played
 * @property integer won
 * @property integer draw
 * @property integer loss
 * @property integer points
 * @property integer power
 * @property integer prediction
 * @property string created_at
 * @property string updated_at
 *
 * @mixin Builder
 */
class Stats extends Model
{
    use HasFactory;

    protected $table = 'stats';

    /**
     * @return Collection
     */
    public function getAllStats(): Collection
    {
        return $this->get();
    }

    /**
     * @param Stats $model
     * @return bool
     */
    public function processSave(self $model): bool
    {
        return $model->save();
    }

    /**
     * @return int
     */
    public function resetAllPredictions(): int
    {
        return $this->getQuery()->update(['prediction' => 0]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo('App\Models\Team', 'team_id');
    }
}
