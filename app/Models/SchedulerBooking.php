<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulerBooking extends Model
{
    use HasFactory;
    public function Schedular()
    {
        return $this->belongsTo(Scheduler::class,'scheduler_id');
    }
}
