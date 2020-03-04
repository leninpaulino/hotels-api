<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Accommodation;
use App\Location;
use Faker\Generator as Faker;

$factory->define(Location::class, function (Faker $faker) {
    return [
        'city' => $faker->city,
        'state' => $faker->state,
        'country' => $faker->country,
        'zip_code' => mb_substr($faker->postcode, 0, 5),
        'address' => $faker->address,
        'accommodation_id' => factory(Accommodation::class),
    ];
});
