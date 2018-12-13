<?php
use Faker\Generator as Faker;

$factory->define(\App\Eloquents\Stock::class, function (Faker $faker) {
    return [
        'account_id'               => '1',
        'article_id'               => $faker->unique()->regexify('[a-z0-9]{20}'),
        'title'                    => $faker->sentence,
        'user_id'                  => $faker->userName,
        'profile_image_url'        => $faker->url,
        'article_created_at'       => $faker->dateTimeThisDecade,
    ];
});

$factory->define(\App\Eloquents\StockTag::class, function (Faker $faker) {
    return [
        'stock_id'                => '1',
        'name'                    => $faker->word,
    ];
});
