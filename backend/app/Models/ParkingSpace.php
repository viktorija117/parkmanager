<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingSpace extends Model
{
    protected $fillable = ['office_id', 'label', 'row', 'notes', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function office() {
        return $this->belongsTo(Office::class);
    }
    public function reservations() {
        return $this->hasMany(Reservation::class);
    }
    public function permanentReservation() {
        return $this->hasOne(PermanentReservation::class);
    }

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function isPermanent(): bool {
        return $this->permanentReservation()->exists();
    }
}
