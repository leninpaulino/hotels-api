<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['city', 'state', 'country', 'zip_code', 'address'];

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }
}
