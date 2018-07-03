<?php

use Faker\Generator as Faker;

$factory->define(App\Sponsorship::class, function (Faker $faker) {
    return [
        'email'        => $faker->email,
        'company_name' => $faker->company,
        'amount'       => $faker->numberBetween(1000, 10000),
    ];
});
