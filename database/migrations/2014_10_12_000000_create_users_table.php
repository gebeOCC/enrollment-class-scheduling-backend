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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id_no')->unique();
            $table->string('application_no')->unique()->nullable();
            $table->string('password');
            $table->enum('user_role', ['faculty', 'student']);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name');
            $table->enum('gender', ['male', 'female']);
            $table->date('birthday');
            $table->string('contact_number')->nullable();
            $table->string('email_address')->nullable();
            $table->string('present_address')->nullable();
            $table->string('zip_code')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
