<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = ['name', 'description', 'questions'];

    protected $casts = ['questions' => 'array'];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['assigned_at', 'completed_at'])
            ->withTimestamps();
    }
}
