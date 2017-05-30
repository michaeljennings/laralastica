<?php

use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;

$factory->define(TestModel::class, function(\Faker\Generator $faker) {
    return [
        'sort_order' => $faker->randomNumber(1),
        'name' => $faker->name,
        'price' => $faker->randomFloat(2),
        'active' => $faker->boolean(),
        'online' => $faker->boolean(),
    ];
});