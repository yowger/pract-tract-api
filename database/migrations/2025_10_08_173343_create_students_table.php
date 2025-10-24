<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('student_id')
                ->unique();
            $table->foreignId('program_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('section_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('advisor_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
