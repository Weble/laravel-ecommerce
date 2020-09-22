<?php

use Faker\Generator as Faker;
use Weble\LaravelEcommerce\Tests\mocks\Product;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'price' => $faker->randomNumber(4),
    ];
});
