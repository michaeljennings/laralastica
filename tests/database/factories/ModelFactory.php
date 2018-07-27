<?php

use Michaeljennings\Laralastica\Tests\Fixtures\TestModel;
use Michaeljennings\Laralastica\Tests\Fixtures\TestSoftDeleteModel;

$factory->define(TestModel::class, function(\Faker\Generator $faker) {
    return [
        'sort_order' => $faker->randomNumber(1),
        'name' => $faker->name,
        'price' => $faker->randomFloat(2),
        'active' => $faker->boolean(),
        'online' => $faker->boolean(),
    ];
});

$factory->define(TestSoftDeleteModel::class, function(\Faker\Generator $faker) {
    return [
        'sort_order' => $faker->randomNumber(1),
        'name' => $faker->name,
        'price' => $faker->randomFloat(2),
        'active' => $faker->boolean(),
        'online' => $faker->boolean(),
    ];
});