<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    protected $fillable = ['name', 'rating', 'category', 'image_url', 'reputation', 'reputation_badge', 'price', 'availability'];

    public function location()
    {
        return $this->hasOne(Location::class);
    }
}
