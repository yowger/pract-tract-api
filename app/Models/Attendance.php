<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'am_status',
        'am_time_in',
        'am_time_out',
        'am_photo_in',
        'am_photo_out',
        'am_lat_in',
        'am_lng_in',
        'am_lat_out',
        'am_lng_out',
        'pm_status',
        'pm_time_in',
        'pm_time_out',
        'pm_photo_in',
        'pm_photo_out',
        'pm_lat_in',
        'pm_lng_in',
        'pm_lat_out',
        'pm_lng_out',
        'duration_minutes',
        'remarks',
        'updated_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
