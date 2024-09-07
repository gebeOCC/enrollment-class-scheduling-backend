<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            $table->string('password');
            $table->enum('user_role', ['faculty', 'student', 'program_head', 'registrar']);
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')->insert([
            'user_id_no' => 'registrar',
            'password' => Hash::make('registrar'),
            'user_role' => 'registrar',
        ]);
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
