<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'course';
    protected $fillable = [
        'department_id',
        'course_name',
        'course_name_abbreviation',
    ];

    public function Department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function YearSection()
    {
        return $this->hasMany(YearSection::class, 'course_id');
    }
}
