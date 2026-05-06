<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PermanentReservation extends Model
{

    use HasFactory;

    protected $fillable = ['user_id', 'parking_space_id', 'admin_notes', 'starts_on', 'ends_on', 'assigned_by'];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function parkingSpace()
    {
        return $this->belongsTo(ParkingSpace::class);
    }
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isActiveOn($date): bool
    {
        $d = \Carbon\Carbon::parse($date);
        if ($this->starts_on && $d->lt($this->starts_on)) return false;
        if ($this->ends_on && $d->gt($this->ends_on)) return false;
        return true;
    }
}
