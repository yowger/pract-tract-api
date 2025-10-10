<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('date');

            $table->enum('am_status', ['present', 'absent', 'late', 'excused'])->nullable();
            $table->time('am_time_in')->nullable();
            $table->time('am_time_out')->nullable();
            $table->string('am_photo_in')->nullable();
            $table->string('am_photo_out')->nullable();
            $table->decimal('am_lat_in', 10, 7)->nullable();
            $table->decimal('am_lng_in', 10, 7)->nullable();
            $table->decimal('am_lat_out', 10, 7)->nullable();
            $table->decimal('am_lng_out', 10, 7)->nullable();

            $table->enum('pm_status', ['present', 'absent', 'late', 'excused'])->nullable();
            $table->time('pm_time_in')->nullable();
            $table->time('pm_time_out')->nullable();
            $table->string('pm_photo_in')->nullable();
            $table->string('pm_photo_out')->nullable();
            $table->decimal('pm_lat_in', 10, 7)->nullable();
            $table->decimal('pm_lng_in', 10, 7)->nullable();
            $table->decimal('pm_lat_out', 10, 7)->nullable();
            $table->decimal('pm_lng_out', 10, 7)->nullable();

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
