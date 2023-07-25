<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    public function Services()
    {
        return $this->belongsToMany(Service::class, 'artist_services');
    }

    public function Artist()
    {
        return $this->belongsToMany(User::class, 'artist_services');
    }

    public function DealServices()
    {
        return $this->belongsToMany(Service::class, 'deal_services')->withPivot('service_id', 'deal_id');
    }

    public function ArtistDeals()
    {
        return $this->belongsToMany(User::class, 'artist_deals')->withPivot('user_id', 'deal_id');
    }
}