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
        Schema::table('student_subjects', function (Blueprint $table) {
            // Drop the old foreign key that references 'subjects'
            $table->dropForeign(['year_section_subjects_id']);

            // Add the new foreign key that references 'year_section_subjects'
            $table->foreign('year_section_subjects_id')
                ->references('id')
                ->on('year_section_subjects')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_subjects', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['year_section_subjects_id']);

            // Restore the old foreign key reference to 'subjects'
            $table->foreign('year_section_subjects_id')
                ->references('id')
                ->on('subjects')
                ->onDelete('restrict');
        });
    }
};
