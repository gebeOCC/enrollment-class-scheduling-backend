<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrolledStudent extends Model
{
    use HasFactory;

    protected $table = 'enrolled_students';
    protected $fillable = [
        'student_id',
        'year_section_id',
        'student_type_id',
        'enroll_type',
        'date_enrolled',
    ];

    public function StudentSubject()
    {
        return $this->hasMany(StudentSubject::class, 'enrolled_students_id');
    }

    public function YearSection()
    {
        return $this->belongsTo(YearSection::class, 'year_section_id');
    }


    public function getStudentCountAttribute()
    {
        return $this->enrolledStudents()->count();
    }
}
