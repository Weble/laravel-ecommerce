<?php

namespace Weble\LaravelEcommerce\Support;

use Iben\Statable\Statable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Weble\LaravelEcommerce\Contracts\StateInterface;
use Weble\LaravelEcommerce\Contracts\TransitionInterface;
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

    public function apply(TransitionInterface $transition, $soft = false, $context = [])
    {
        if ($this->statableApply($transition->value(), $soft, $context)) {
            $this->save();

            return $this;
        }

        throw new InvalidTransitionException($transition, $this);
    }

    public function stateIs(): string
    {
        return $this->state()->value();
    }

    public function state(): StateInterface
    {
        $graph = $this->getGraph();
        $property = config('state-machine.' . $graph . '.property_path', 'state');

        return $this->{$property};
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

    public function possibleTransitions(): Collection
    {
        if ($this->getKey() === null) {
            return collect([]);
        }

        return collect($this->stateMachine()->getPossibleTransitions());
    }

    public function hasPossibleTransition($name): bool
    {
        return $this->possibleTransitions()->contains($name);
    }
}
