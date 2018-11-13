<?php

use Faker\Generator as Faker;

$factory->define(\App\Eloquents\Category::class, function (Faker $faker) {
    return [
        'account_id'       => '1',
    ];
});

$factory->define(\App\Eloquents\CategoryName::class, function (Faker $faker) {
    return [
        'category_id'       => '1',
        'name'              => $faker->word,
    ];
});
