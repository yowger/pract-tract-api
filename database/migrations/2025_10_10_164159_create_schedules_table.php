<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            $table->enum('day_of_week', [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday'
            ]);

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->time('am_time_in')->nullable();
            $table->time('am_time_out')->nullable();
            $table->boolean('am_require_photo_in')->default(false);
            $table->boolean('am_require_photo_out')->default(false);
            $table->boolean('am_require_location_in')->default(false);
            $table->boolean('am_require_location_out')->default(false);

            $table->time('pm_time_in')->nullable();
            $table->time('pm_time_out')->nullable();
            $table->boolean('pm_require_photo_in')->default(false);
            $table->boolean('pm_require_photo_out')->default(false);
            $table->boolean('pm_require_location_in')->default(false);
            $table->boolean('pm_require_location_out')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
