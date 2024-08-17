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
        Schema::create('school_year', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('semester_id');
            $table->foreign('semester_id')->references('id')->on('course')->onDelete('cascade');
            $table->string('school_year');
            $table->enum('enrollment_status', ['ongoing', 'ended']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_year');
    }
};
