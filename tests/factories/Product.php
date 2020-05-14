<?php

use Faker\Generator as Faker;

$factory->define(\Weble\LaravelEcommerce\Tests\mocks\Product::class, function (Faker $faker) {
    return [
        'price' => $faker->randomNumber(4),
    ];
});
