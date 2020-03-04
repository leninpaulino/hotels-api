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
