<?php

namespace App\Http\Controllers\API;

use App\Accommodation;
use App\Http\Controllers\Controller;
use App\Http\Resources\Accommodation as AccommodationResource;
use App\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccommodationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return AccommodationResource::collection(auth()->user()->accommodations);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $this->validated();

        $accommodation = auth()->user()->accommodations()->create(Arr::except($validatedData, ['location']));

        $accommodation->location()->create($validatedData['location']);

        return (new AccommodationResource($accommodation))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Accommodation $accommodation
     * @return \Illuminate\Http\Response
     */
    public function show(Accommodation $accommodation)
    {
        return new AccommodationResource($accommodation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Accommodation $accommodation
     * @return \Illuminate\Http\Response
     */
    public function update(Accommodation $accommodation)
    {
        $validatedData = $this->validated();

        $accommodation->update(Arr::except($validatedData, ['location']));

        $accommodation->location()->update($validatedData['location']);

        return (new AccommodationResource($accommodation))->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Accommodation $accommodation
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Accommodation $accommodation)
    {
        $accommodation->delete();

        return response()->noContent();
    }

    /**
     * Book a room from the given accommodation.
     *
     * @param  Accommodation $accommodation
     *
     * @return \Illuminate\Http\Response
     */
    public function book(Accommodation $accommodation)
    {
        $accommodation->availability--;

        $accommodation->update();

        return response()->noContent();
    }

    public function validated()
    {
        $data = [
            'name' => request('name'),
            'rating' => request('rating'),
            'category' => request('category'),
            'image_url' => request('image'),
            'reputation' => request('reputation'),
            'reputation_badge' => request('reputationBadge'),
            'price' => request('price'),
            'availability' => request('availability'),
            'location' =>  [
                'city' => request('location.city'),
                'state' => request('location.state'),
                'country' => request('location.country'),
                'zip_code' => request('location.zip_code'),
                'address' => request('location.address'),
            ],
        ];

        request()->merge($data);

        $validator = Validator::make(request()->all(), [
            'name' => [
                'required',
                'min:10',
                'regex:/^(?!.*(Free|Offer|Book|Website)).*$/i',
            ],
            'rating' => 'required|integer|min:0|max:5',
            'category' => [
                'required',
                'string',
                Rule::in(['hotel', 'alternative', 'hostel', 'lodge', 'resort', 'guesthouse']),
            ],
            'image_url' => 'required|url',
            'reputation' => 'required|integer|min:0|max:1000',
            'reputation_badge' => [
                'required',
                Rule::in(['red', 'yellow', 'green']),
                function ($attribute, $value, $fail) {
                    $reputation = request('reputation');
                    if ($reputation <= 500) {
                        $reputationBadge = 'red';
                    } elseif ($reputation <= 799) {
                        $reputationBadge = 'yellow';
                    } else {
                        $reputationBadge = 'green';
                    }

                    if ($value !== $reputationBadge) {
                        $fail($attribute.' is invalid.');
                    }
                },
            ],
            'price' => 'required|integer',
            'availability' => 'required|integer',
            'location.city' => 'required',
            'location.state' => 'required',
            'location.country' => 'required',
            'location.zip_code' => 'required|integer|digits:5',
            'location.address' => 'required',
        ]);

        return $validator->validate();
    }
}
