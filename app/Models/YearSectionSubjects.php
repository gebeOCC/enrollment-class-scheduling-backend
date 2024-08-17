<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearSectionSubjects extends Model
{
    use HasFactory;

    protected $table = 'year_section_subjects';
    protected $fillable = [
        'year_section_id',
        'faculty_id',
        'room_id',
        'subject_id',
        'class_code',
        'day',
        'start_time',
        'end_time',
    ];
}
