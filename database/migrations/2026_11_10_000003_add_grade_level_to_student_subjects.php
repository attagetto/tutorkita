<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_subjects', function (Blueprint $table): void {
            $table->foreignId('grade_level_id')->nullable()->after('subject_id')->constrained('grade_levels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_subjects', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('grade_level_id');
        });
    }
};
