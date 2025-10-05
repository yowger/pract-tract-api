<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'address', 'phone', 'email', 'is_active'];

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }
}
