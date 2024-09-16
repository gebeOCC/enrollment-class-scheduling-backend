<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EnrolledStudent;

class YearSection extends Model
{
    use HasFactory;

    protected $table = 'year_section';
    protected $fillable = [
        'school_year_id',
        'course_id',
        'year_level_id',
        'section',
        'max_students',
    ];

    public function enrolledStudents()
    {
        return $this->hasMany(EnrolledStudent::class, 'year_section_id');
    }

    public function getStudentCountAttribute()
    {
        return $this->enrolledStudents()->count();
    }
}
