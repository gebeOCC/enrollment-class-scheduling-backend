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
        Schema::create('user_information', function (Blueprint $table) {
            $table->id();
            // Fix the foreign key definition
            $table->string('user_id_no'); // Assuming user_id is unique and not auto-incrementing
            $table->foreign('user_id_no')->references('user_id_no')->on('users')->onDelete('cascade');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name');
            $table->enum('gender', ['male', 'female']);
            $table->date('date_of_birth');
            $table->string('contact_number');
            $table->string('email_address');
            $table->string('present_address');
            $table->string('zip_code');

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
        Schema::dropIfExists('user_information');
    }
};
