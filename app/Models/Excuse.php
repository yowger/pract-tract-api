<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Excuse extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'attendance_id',
        'title',
        'description',
        'status',
        'attachments',
        'date',
    ];

    protected $casts = [
        'attachments' => 'array',
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function getAttachmentsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }
}
