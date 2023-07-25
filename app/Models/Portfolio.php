<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $appends = [
        'absolute_image_url'
    ];

    public function getAbsoluteImageUrlAttribute()
    {
        return url($this->attributes['image_url']);
    }
}
