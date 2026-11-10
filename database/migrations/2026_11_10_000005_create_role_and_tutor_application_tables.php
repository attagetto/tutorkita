<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table): void {
            $table->string('user_id', 80)->primary();
            $table->string('role', 20)->default('student')->index();
            $table->timestamps();
        });

        Schema::create('user_role_changes', function (Blueprint $table): void {
            $table->id();
            $table->string('user_id', 80)->index();
            $table->string('actor_id', 80)->nullable()->index();
            $table->string('old_role', 20)->nullable();
            $table->string('new_role', 20);
            $table->string('source', 40);
            $table->timestamps();
        });

        Schema::create('tutor_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('user_id', 80)->unique();
            $table->string('applicant_name', 120);
            $table->string('education_level', 80);
            $table->string('institution', 180);
            $table->string('program', 180);
            $table->unsignedSmallInteger('graduation_year');
            $table->text('bio');
            $table->text('experience')->nullable();
            $table->text('additional_info')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->string('reviewed_by', 80)->nullable()->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tutor_application_subject', function (Blueprint $table): void {
            $table->foreignId('tutor_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->primary(['tutor_application_id', 'subject_id']);
        });

        Schema::create('tutor_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('user_id', 80)->unique();
            $table->string('education_level', 80)->nullable();
            $table->string('institution', 180)->nullable();
            $table->string('program', 180)->nullable();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->text('bio')->nullable();
            $table->text('experience')->nullable();
            $table->text('additional_info')->nullable();
            $table->string('avatar_path')->nullable();
            $table->json('availability')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('tutor_profile_subject', function (Blueprint $table): void {
            $table->foreignId('tutor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->primary(['tutor_profile_id', 'subject_id']);
        });

        Schema::create('tutor_application_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tutor_application_id')->constrained()->cascadeOnDelete();
            $table->string('actor_id', 80)->nullable()->index();
            $table->string('action', 24);
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_application_reviews');
        Schema::dropIfExists('tutor_profile_subject');
        Schema::dropIfExists('tutor_profiles');
        Schema::dropIfExists('tutor_application_subject');
        Schema::dropIfExists('tutor_applications');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('user_role_changes');
    }
};
