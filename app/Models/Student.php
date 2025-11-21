<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'program_id',
        'section_id',
        'advisor_id',
        'company_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function advisor()
    {
        return $this->belongsTo(Advisor::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function evaluationAnswers()
    {
        return $this->hasMany(EvaluationAnswer::class);
    }
}
