<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'webUrl',
        'description',
        'latitude',
        'longitude',
        'image',
        'contacts',
        'date_start',
        'date_stop',
    ];

}
