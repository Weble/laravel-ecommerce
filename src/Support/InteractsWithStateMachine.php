<?php

namespace Weble\LaravelEcommerce\Support;

use Iben\Statable\Statable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Weble\LaravelEcommerce\Order\StateHistory;

trait InteractsWithStateMachine
{
    use Statable {
        apply as statableApply;
    }

    public function __call($name, $arguments)
    {
        if ($this->hasPossibleTransition($name)) {
            $this->apply($name);
            return $this;
        }

        return parent::__call($name, $arguments);
    }

    public function apply($transition, $soft = false, $context = [])
    {
        if ($this->statableApply($transition, $soft, $context)) {
            $this->save();
            return $this;
        }

        throw new InvalidTransitionException($transition, $this);
    }

    public function stateHistory(): MorphMany
    {
        return $this->morphMany(config('ecommerce.classes.stateHistoryModel', StateHistory::class), 'model');
    }

    public function addHistoryLine(array $transitionData)
    {
        if ($this->getKey()) {
            $transitionData['actor_id'] = $this->getActorId();
            $this->stateHistory()->create($transitionData);
        }
    }

    abstract protected function getGraph(): string;

    protected function saveBeforeTransition(): bool
    {
        return true;
    }

    public function hasPossibleTransition($name): bool
    {
        return collect($this->stateMachine()->getPossibleTransitions())->contains($name);
    }
}
