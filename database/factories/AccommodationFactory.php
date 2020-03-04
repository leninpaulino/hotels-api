<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Accommodation;
use App\User;
use Faker\Generator as Faker;

$factory->define(Accommodation::class, function (Faker $faker) {
    $reputation = $faker->numberBetween(0, 1000);
    if ($reputation <= 500) {
        $reputationBadge = 'red';
    } elseif ($reputation <= 799) {
        $reputationBadge = 'yellow';
    } else {
        $reputationBadge = 'green';
    }

    return [
        'name' => $faker->name(),
        'rating' => $faker->numberBetween(0, 5),
        'category' => $faker->randomElement(['hotel', 'alternative', 'hostel', 'lodge', 'resort', 'guesthouse']),
        'image_url' => $faker->imageUrl(),
        'reputation' => $reputation,
        'reputation_badge' => $reputationBadge,
        'price' => $faker->randomNumber(),
        'availability' =>  $faker->randomNumber(),
        'user_id' => factory(User::class),
    ];
});
