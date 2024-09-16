<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrolledStudent extends Model
{
    use HasFactory;

    protected $table = 'enrolled_students';
    protected $fillable = [
        'student_id_no',
        'year_section_id',
        'student_type_id',
        'enroll_type',
        'date_enrolled',
    ];

    public function enrolledStudents()
    {
        return $this->hasMany(EnrolledStudent::class, 'year_section_id');
    }

    // Accessor for the student count
    public function getStudentCountAttribute()
    {
        return $this->enrolledStudents()->count();
    }
}
