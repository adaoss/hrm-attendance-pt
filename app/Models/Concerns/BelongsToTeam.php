<?php

namespace App\Models\Concerns;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTeam
{
    /**
     * Boot the BelongsToTeam trait for a model.
     */
    protected static function bootBelongsToTeam(): void
    {
        static::addGlobalScope('team', function (Builder $builder) {
            if (auth()->check() && auth()->user()->team_id) {
                $builder->where(static::getTable() . '.team_id', auth()->user()->team_id);
            }
        });

        static::creating(function (Model $model) {
            if (auth()->check() && auth()->user()->team_id && !$model->team_id) {
                $model->team_id = auth()->user()->team_id;
            }
        });
    }

    /**
     * Get the team that the model belongs to.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
