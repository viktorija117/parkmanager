<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Office extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'address', 'timezone'];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function parkingSpaces()
    {
        return $this->hasMany(ParkingSpace::class);
    }
}
