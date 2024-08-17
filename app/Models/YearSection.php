<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearSection extends Model
{
    use HasFactory;

    protected $table = 'year_section';
    protected $fillable = [
        'school_year_id',
        'course_id',
        'year_level_id',
        'section',
    ];
}
