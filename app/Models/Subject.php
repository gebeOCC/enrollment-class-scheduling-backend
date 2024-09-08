<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'subjects';
    protected $fillable = [
        'subject_code',
        'descriptive_title',
        'credit_units',
        'lecture_hours',
        'laboratory_hours',
    ];
}
