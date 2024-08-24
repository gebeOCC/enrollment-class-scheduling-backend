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
        Schema::create('faculty_role', function (Blueprint $table) {
            $table->id();
            $table->string('faculty_id_no');
            $table->foreign('faculty_id_no')->references('user_id_no')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department')->onDelete('cascade');
            $table->enum('faculty_role', ['program_head', 'registrar'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_role');
    }
};
