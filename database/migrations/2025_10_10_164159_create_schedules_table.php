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

            $table->json('day_of_week')->nullable();

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

            $table->integer('am_grace_period_minutes')->default(0);
            $table->integer('pm_grace_period_minutes')->default(0);
            $table->boolean('allow_early_in')->default(false);
            $table->integer('early_in_limit_minutes')->default(0);
            $table->integer('am_undertime_grace_minutes')->default(0);
            $table->integer('pm_undertime_grace_minutes')->default(0);

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->integer('radius')->default(30);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
