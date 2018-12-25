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

$factory->define(\App\Eloquents\CategoryStock::class, function (Faker $faker) {
    return [
        'category_id'       => '1',
        'article_id'        => $faker->unique()->regexify('[a-z0-9]{20}'),
    ];
});
