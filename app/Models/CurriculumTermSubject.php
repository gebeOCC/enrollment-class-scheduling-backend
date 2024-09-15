<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurriculumTermSubject extends Model
{
    use HasFactory;

    protected $table = 'curriculum_term_subjects';
    protected $fillable = [
        'curriculum_term_id',
        'subject_id',
        'pre_requisite_subject_id',
    ];
}
