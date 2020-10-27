<?php

namespace Weble\LaravelEcommerce\Support;

use Iben\Statable\Statable;
use RuntimeException;

class InvalidTransitionException extends RuntimeException
{
    protected Statable $model;
    protected string $transition;

    public function __construct(string $transition, Statable $model)
    {
        $this->transition = $transition;
        $this->model      = $model;

        parent::__construct("Invalid transition {$transition} applied to model " . get_class($model));
    }
}
