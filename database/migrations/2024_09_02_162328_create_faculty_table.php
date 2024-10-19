<?php

use App\Models\User;
use App\Models\UserInformation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('faculty', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->foreign('faculty_id')->references('id')->on('users')->onDelete('cascade')->unique();
            $table->unsignedBigInteger('department_id');
            $table->foreign('department_id')->references('id')->on('department')->onDelete('cascade');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });


        $user = User::create([
            'user_id_no' => 'registrar',
            'password' => Hash::make('registrar'),
            'user_role' => 'registrar',
        ]);

        UserInformation::create([
            'user_id' => $user->id,
            'first_name'  => 'first name',
            'last_name'  => 'last name',
            'middle_name'  => 'middle name',
            'gender'  => 'male',
            'birthday'  => '2003-08-12',
            'contact_number'  => '09993334444',
            'email_address'  => 'email@example.com',
            'present_address'  => 'zip code',
            'zip_code'  => '9999',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty');
    }
};
