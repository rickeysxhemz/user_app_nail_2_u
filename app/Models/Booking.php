<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

//    public function Service()
//    {
//        return $this->belongsTo(Service::class);
//    }

    public function Transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function BookingService()
    {
        return $this->belongsToMany(Service::class, 'booking_services')->withPivot('service_id', 'price');
    }

    public function Artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }

    public function Client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
    
    public function Schedule()
    {
        return $this->belongsTo(Scheduler::class, 'started_at');
    }
    
    public function SchedulerBooking()
    {
        return $this->belongsToMany(Scheduler::class, 'scheduler_bookings')->withPivot('date');
    }
    
    public function BookingLocation()
    {
        return $this->hasOne(BookingLocation::class);
    }
}
