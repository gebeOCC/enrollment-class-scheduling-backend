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
        Schema::create('student_pre_enrollment_list_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pre_enrollment_id');
            $table->foreign('pre_enrollment_id')->references('id')->on('student_pre_enrollment_list')->onDelete('cascade');
            $table->unsignedBigInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_pre_enrollment_list_subjects');
    }
};
