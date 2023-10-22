<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPostedService extends Model
{
    use HasFactory;
    
    public function Client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }
    public function Service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    
    public function PostService()
    {
        return $this->belongsToMany(Service::class, 'post_services')->withPivot('service_id');
    }

    public function Schedule()
    {
        return $this->belongsTo(Scheduler::class, 'time');
    }
}