<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurriculumTerm extends Model
{
    use HasFactory;

    protected $table = 'curriculum_term';
    protected $fillable = [
        'currriculum_id',
        'year_level_id',
        'semester_id',
    ];
}
