<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_materials', function (Blueprint $table): void {
            $table->string('created_by')->nullable()->index();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('learning_materials', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropIndex(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
