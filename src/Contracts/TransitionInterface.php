<?php

namespace Weble\LaravelEcommerce\Contracts;

interface TransitionInterface
{
    public function value(): string;

    public function name(): string;
}
