<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationAnswer extends Model
{
    protected $fillable = [
        'evaluation_id',
        'student_id',
        'answers',
        'submitted_by',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
