<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['title', 'schema', 'created_by'];

    protected $casts = [
        'schema' => 'array',
    ];

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_form');
    }

    public function responses()
    {
        return $this->hasMany(FormResponse::class);
    }
}
