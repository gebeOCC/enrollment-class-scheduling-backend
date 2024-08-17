<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfomation extends Model
{
    use HasFactory;

    protected $table = 'user_information';
    protected $fillable = [
        'user_id_no',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'date_of_birth',
        'contact_number',
        'email_address',
        'present_address',
        'zip_code',
        'department_id',
        'faculty_role',
    ];
}
