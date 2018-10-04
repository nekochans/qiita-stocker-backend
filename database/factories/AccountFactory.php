<?php

use Faker\Generator as Faker;

$factory->define(App\Eloquents\Account::class, function (Faker $faker) {
    return [];
});

$factory->define(App\Eloquents\QiitaAccount::class, function (Faker $faker) {
    return [
        'account_id'       => '1',
        'qiita_account_id' => '1'
    ];
});

$factory->define(App\Eloquents\AccessToken::class, function (Faker $faker) {
    return [
        'account_id'   => '1',
        'access_token' => 'accesstoken655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5'
    ];
});

$factory->define(App\Eloquents\LoginSession::class, function (Faker $faker) {
    return [
        'id'         => $faker->uuid(),
        'account_id' => '1',
        'expired_on' => '2018-10-01 00:00:00'
    ];
});
