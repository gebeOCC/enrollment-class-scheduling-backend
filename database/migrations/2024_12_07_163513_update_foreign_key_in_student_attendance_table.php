<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing foreign key
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropForeign(['year_section_subjects_id']);
        });

        // Add the new foreign key reference
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->foreign('year_section_subjects_id')->references('id')->on('year_section_subjects')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the foreign key change by dropping the new one and restoring the old one
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropForeign(['year_section_subjects_id']);
        });

        Schema::table('student_attendance', function (Blueprint $table) {
            $table->foreign('year_section_subjects_id')->references('id')->on('subjects')->onDelete('restrict');
        });
    }
};
