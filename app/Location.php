<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }
}
