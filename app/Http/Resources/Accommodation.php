<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Accommodation extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'rating' => $this->rating,
            'category' => $this->category,
            'image' => $this->image_url,
            'reputation' => $this->reputation,
            'reputationBadge' => $this->reputation_badge,
            'price' => $this->price,
            'availability' => $this->availability,
            'location' => [
                'city' => $this->location->city,
                'state' => $this->location->state,
                'country' => $this->location->country,
                'zip_code' => $this->location->zip_code,
                'address' => $this->location->address,
            ],
        ];
    }
}
