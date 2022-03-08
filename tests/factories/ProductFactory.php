<?php

namespace Weble\LaravelEcommerce\Tests\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Weble\LaravelEcommerce\Tests\mocks\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Weble\LaravelEcommerce\Tests\mocks\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'price' => $this->faker->randomNumber(4),
        ];
    }
}

;
