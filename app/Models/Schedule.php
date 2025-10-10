<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'company_id',
        'day_of_week',
        'start_date',
        'end_date',
        'am_time_in',
        'am_time_out',
        'am_require_photo_in',
        'am_require_photo_out',
        'am_require_location_in',
        'am_require_location_out',
        'pm_time_in',
        'pm_time_out',
        'pm_require_photo_in',
        'pm_require_photo_out',
        'pm_require_location_in',
        'pm_require_location_out',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
