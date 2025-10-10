<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['title', 'schema', 'created_by'];

    protected $casts = [
        'schema' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses()
    {
        return $this->hasMany(FormResponse::class);
    }
}
