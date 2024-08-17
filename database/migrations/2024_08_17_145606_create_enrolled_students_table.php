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
        Schema::create('enrolled_students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id_no');
            $table->foreign('student_id_no')->references('user_id_no')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('year_section_id');
            $table->foreign('year_section_id')->references('id')->on('year_section')->onDelete('cascade');
            $table->unsignedBigInteger('student_type_id');
            $table->foreign('student_type_id')->references('id')->on('student_pre_enrollment_list')->onDelete('cascade');
            $table->enum('enroll_type', ['on-time', 'late']);
            $table->date('date_enrolled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrolled_students');
    }
};
