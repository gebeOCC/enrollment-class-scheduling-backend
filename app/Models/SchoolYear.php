<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    use HasFactory;

    protected $table = 'school_year';
    protected $fillable = [
        'semester_id',
        'school_year',
        'enrollment_status',
    ];
}
