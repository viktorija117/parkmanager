<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'slug'];
    public function offices() {
        return $this->hasMany(Office::class);
    }
}
