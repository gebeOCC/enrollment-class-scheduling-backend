<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    use HasFactory;

    protected $table = 'school_years';
    protected $fillable = [
        'semester_id',
        'start_year',
        'end_year',
        'start_date',
        'end_date',
        'is_current',
    ];
}
