<?php

namespace App\Models;

use App\Helper\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $appends = ['transaction_time', 'transaction_day'];

    public function Booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getTransactionTimeAttribute()
    {
        return date("h:i a", strtotime($this->attributes['created_at']));
    }

    public function getTransactionDayAttribute()
    {
        return Helper::getDays($this->attributes['created_at']);
    }
}
