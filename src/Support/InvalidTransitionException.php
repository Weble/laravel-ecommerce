<?php

namespace Weble\LaravelEcommerce\Support;

use Iben\Statable\Statable;
use RuntimeException;
use Weble\LaravelEcommerce\Contracts\TransitionInterface;

class InvalidTransitionException extends RuntimeException
{
    protected Statable $model;
    protected TransitionInterface $transition;

    public function __construct(TransitionInterface $transition, Statable $model)
    {
        $this->transition = $transition;
        $this->model      = $model;

        parent::__construct("Invalid transition {$transition} applied to model " . get_class($model));
    }
}
