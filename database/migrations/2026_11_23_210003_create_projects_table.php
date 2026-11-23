<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 30)->default('saas');
            $table->string('priority', 20)->default('medium');
            $table->string('status', 20)->default('active');
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('deadline');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('primary_project_id')
                ->nullable()
                ->after('availability_status')
                ->constrained('projects')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('primary_project_id');
        });

        Schema::dropIfExists('projects');
    }
};
