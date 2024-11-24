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

    public function Subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }


    public function Instructor()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function UserInformation()
    {
        return $this->belongsTo(UserInformation::class, 'faculty_id');
    }

    public function Room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function YearSection()
    {
        return $this->belongsTo(YearSection::class, 'year_section_id');
    }
}
