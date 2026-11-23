<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transcript_segments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('speaker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('start_second');
            $table->string('language', 5)->default('en');
            $table->text('text');

            $table->index(['meeting_id', 'language', 'start_second']);

            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText('text');
            }
        });

        Schema::create('meeting_summaries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('language', 5)->default('en');
            $table->json('key_points');
            $table->json('action_items');
            $table->timestamp('generated_at')->useCurrent();

            $table->unique(['meeting_id', 'language']);
        });

        Schema::create('meeting_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->unique(['meeting_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_notes');
        Schema::dropIfExists('meeting_summaries');
        Schema::dropIfExists('transcript_segments');
    }
};
