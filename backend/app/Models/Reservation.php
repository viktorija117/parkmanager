<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = ['user_id', 'parking_space_id', 'date', 'status', 'cancellation_reason', 'cancelled_by', 'cancelled_at'];

    protected $casts = [
        'date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function parkingSpace()
    {
        return $this->belongsTo(ParkingSpace::class);
    }
    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopeConfirmed($q)
    {
        return $q->where('status', 'confirmed');
    }
    public function scopeForDate($q, $date)
    {
        return $q->whereDate('date', $date);
    }
    public function scopeUpcoming($q)
    {
        return $q->where('date', '>=', today());
    }
}
