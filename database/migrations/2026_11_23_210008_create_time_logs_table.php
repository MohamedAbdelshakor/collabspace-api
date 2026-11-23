<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('minutes');
            $table->date('logged_on');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'logged_on']);
            $table->index('task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_logs');
    }
};
