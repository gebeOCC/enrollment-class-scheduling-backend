<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubject extends Model
{
    use HasFactory;

    protected $table = 'student_subjects';
    protected $fillable = [
        'enrolled_students_id',
        'year_section_subjects_id',
    ];

    public function YearSectionSubjects()
    {
        return $this->belongsTo(YearSectionSubjects::class, 'year_section_subjects_id');
    }
}
