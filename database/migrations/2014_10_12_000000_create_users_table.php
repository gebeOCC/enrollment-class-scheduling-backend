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
            'user_id_no' => '1',
            'password' => Hash::make('password'),
            'user_role' => 'registrar',
        ]);

        DB::table('users')->insert([
            'user_id_no' => '2',
            'password' => Hash::make('password'),
            'user_role' => 'program_head',
        ]);

        DB::table('users')->insert([
            'user_id_no' => '3',
            'password' => Hash::make('password'),
            'user_role' => 'program_head',
        ]);

        DB::table('users')->insert([
            'user_id_no' => '4',
            'password' => Hash::make('password'),
            'user_role' => 'program_head',
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
