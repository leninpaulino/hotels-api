<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    public function location()
    {
        return $this->hasOne(Location::class);
    }
}
