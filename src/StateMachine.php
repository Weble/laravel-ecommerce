<?php

namespace Weble\LaravelEcommerce;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Weble\LaravelEcommerce\Contracts\StateInterface;

class StateMachine extends \Sebdesign\SM\StateMachine\StateMachine
{
    public function getState(): string
    {
        $accessor = new PropertyAccessor();
        $state = $accessor->getValue($this->object, $this->config['property_path']);

        if ($state instanceof StateInterface) {
            return $state->value();
        }

        return $state;
    }
}
