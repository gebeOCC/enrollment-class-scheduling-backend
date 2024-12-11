<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $fillable = [
        'department_id  ',
        'room_name',
    ];

    public function Schedules()
    {
        return $this->hasMany(YearSectionSubjects::class, 'room_id');
    }

    public function SecondarySchedules()
    {
        return $this->hasMany(SubjectSecondarySchedule::class, 'room_id');
    }
}
