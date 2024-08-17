<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\YearLevel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('year_level', function (Blueprint $table) {
            $table->id();
            $table->string('year_level');
            $table->timestamps();
        });
        foreach (range(1, 4) as $level) {
            YearLevel::create(['year_level' => $level]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('year_level');
    }
};
