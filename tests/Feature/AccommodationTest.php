<?php

namespace Tests\Feature;

use App\Accommodation;
use App\Http\Resources\Accommodation as AccommodationResource;
use App\Location;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class AccommodationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->actingAs($this->user, 'api');
    }

    public function test_user_can_create_an_accommodation()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields());

        $response->assertStatus(201);

        $resourceResponse = $this->createResource($this->user->accommodations->last())->response()->getData(true);
        $response->assertJson($resourceResponse);

        $this->assertDatabaseHas('accommodations', [
            'name' => $this->validFields()['name'],
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('locations', $this->validFields()['location']);
    }

    public function test_user_can_see_accommodations()
    {
        $accommodations = factory(Accommodation::class, 5)->create()->each(function ($accommodation) {
            $accommodation->location()->save(factory(Location::class)->make());
        });

        $this->user->accommodations()->saveMany($accommodations);

        $response = $this->getJson(route('accommodations.index'));

        $response->assertStatus(200);

        $response->assertJsonCount(5, 'data');
    }

    public function test_user_can_see_specific_accommodation()
    {
        $accommodation = $this->createAccommodation();

        $response = $this->getJson(route('accommodations.show', $accommodation->id));

        $response->assertStatus(200);

        $resourceResponse = (new AccommodationResource($accommodation))->response()->getData(true);
        $response->assertJson($resourceResponse);
    }

    public function test_user_can_update_an_accommodation()
    {
        $accommodation = $this->createAccommodation();

        $accommodation->name = 'Updated Name';
        $accommodation->location->city = 'Santo Domingo';

        $resource = $this->createResource($accommodation)->response()->getData(true);

        $response = $this->putJson(route('accommodations.update', $accommodation->id), $resource['data']);

        $response->assertStatus(200);

        $response->assertExactJson($resource);
    }

    public function test_user_can_delete_an_accommodation()
    {
        $accommodation = $this->createAccommodation();

        $accommodationId = $accommodation->id;
        $locationId = $accommodation->location->id;

        $response = $this->deleteJson(route('accommodations.destroy', $accommodation->id));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('accommodations', [
            'id' => $accommodationId,
        ]);

        $this->assertDatabaseMissing('locations', [
            'id' => $locationId,
        ]);
    }

    public function test_user_can_book_an_accommodation()
    {
        $accommodation = factory(Accommodation::class)->create(['availability' => 15]);

        $this->user->accommodations()->save($accommodation);

        $response = $this->postJson(route('accommodations.book', $accommodation->id));

        $response->assertStatus(204);

        $this->assertDatabaseHas('accommodations', [
            'id' => $accommodation->id,
            'availability' => 14,
        ]);
    }

    public function test_name_cannot_contain_invalid_words()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['name' => 'Limited Offer']));

        $this->assertResponseHasInvalidParam($response, 'name');
    }

    public function test_name_should_be_longer_than_10()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['name' => 'too short']));

        $this->assertResponseHasInvalidParam($response, 'name');
    }

    public function test_rating_should_be_less_than_or_equal_to_five()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['rating' => 10]));

        $this->assertResponseHasInvalidParam($response, 'rating');
    }

    public function test_rating_should_be_more_than_or_equal_to_zero()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['rating' => -10]));

        $this->assertResponseHasInvalidParam($response, 'rating');
    }

    public function test_image_should_be_a_valid_url()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['image' => 'beautiful-room.jpg']));

        $this->assertResponseHasInvalidParam($response, 'image_url');
    }

    public function test_category_should_be_valid_one()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['category' => 'hotel*****']));

        $this->assertResponseHasInvalidParam($response, 'category');
    }

    public function test_reputation_should_be_less_than_or_equal_to_1000()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['reputation' => 1001]));

        $this->assertResponseHasInvalidParam($response, 'reputation');
    }

    public function test_reputation_should_be_more_than_or_equal_to_zero()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['reputation' => -100]));

        $this->assertResponseHasInvalidParam($response, 'reputation');
    }

    public function test_reputation_badge_should_be_red_if_reputation_is_less_than_500()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['reputation' => 255, 'reputationBadge' => 'green']));

        $this->assertResponseHasInvalidParam($response, 'reputation_badge');
    }

    public function test_reputation_badge_should_be_yellow_if_reputation_is_less_than_799()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['reputation' => 600, 'reputationBadge' => 'red']));

        $this->assertResponseHasInvalidParam($response, 'reputation_badge');
    }

    public function test_reputation_badge_should_be_green_if_reputation_is_more_than_799()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['reputation' => 999, 'reputationBadge' => 'yellow']));

        $this->assertResponseHasInvalidParam($response, 'reputation_badge');
    }

    public function test_price_should_be_an_integer()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['price' => 'super cheap']));

        $this->assertResponseHasInvalidParam($response, 'price');
    }

    public function test_availability_should_be_an_integer()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['availability' => 'a lot']));

        $this->assertResponseHasInvalidParam($response, 'availability');
    }

    public function test_location_zipcode_should_be_five_digits()
    {
        $response = $this->postJson(route('accommodations.store'), $this->validFields(['location' => ['zip_code' => 123456]]));

        $this->assertResponseHasInvalidParam($response, 'location.zip_code');
    }

    /**
     * Helper method to assert a response includes the provided invalid param.
     */
    public function assertResponseHasInvalidParam(TestResponse $response, string $param): void
    {
        $response->assertStatus(422);

        $response->assertJsonStructure([
            'invalid-params' => [
                $param,
            ],
        ]);
    }

    /**
     * Returns an array of valid data to be used when interacting with the API.
     */
    public function validFields(array $overrride = []): array
    {
        return array_merge([
            'name' => 'Example name',
            'rating' => 5,
            'category' => 'hotel',
            'image' => 'https://image-url.com',
            'reputation' => 990,
            'reputationBadge' => 'green',
            'price' => 1000,
            'availability' => 10,
            'location' => [
                'city' => 'Cuernavaca',
                'state' => 'Morelos',
                'country' => 'Mexico',
                'zip_code' => 62448,
                'address' => 'Boulevard Díaz Ordaz No. 9 Cantarranas',
            ],
        ], $overrride);
    }

    /**
     * Create an accommodation item to be used with the tests.
     */
    public function createAccommodation(): Accommodation
    {
        $accommodation = $this->user->accommodations()->create([
            'name' => 'Example name',
            'rating' => 5,
            'category' => 'hotel',
            'image_url' => 'https://image-url.com',
            'reputation' => 990,
            'reputation_badge' => 'green',
            'price' => 1000,
            'availability' => 10,
        ]);

        $accommodation->location()->create([
            'city' => 'Cuernavaca',
            'state' => 'Morelos',
            'country' => 'Mexico',
            'zip_code' => 62448,
            'address' => 'Boulevard Díaz Ordaz No. 9 Cantarranas',
        ]);

        return $accommodation;
    }

    /**
     * Create an Accommodation resource to be used with tests.
     */
    public function createResource(Accommodation $accommodation = null): AccommodationResource
    {
        if ($accommodation == null) {
            $accommodation = $this->createAccommodation();
        }

        return new AccommodationResource($accommodation);
    }
}
