<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 30);
            $table->text('note')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('pending');
            $table->json('data')->nullable();
            $table->text('ai_summary')->nullable();
            $table->string('csv_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('failed_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('daily_metrics', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->string('scope_type', 50);
            $table->unsignedBigInteger('scope_id');
            $table->string('metric', 50);
            $table->decimal('value', 12, 2)->default(0);

            $table->unique(['date', 'scope_type', 'scope_id', 'metric']);
        });

        Schema::create('project_progress_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('progress');

            $table->unique(['project_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_progress_snapshots');
        Schema::dropIfExists('daily_metrics');
        Schema::dropIfExists('reports');
    }
};
