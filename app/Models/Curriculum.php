<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    use HasFactory;

    protected $table = 'curriculum';
    protected $fillable = [
        'course_id',
        'school_year_start',
        'school_year_end',
    ];
}
