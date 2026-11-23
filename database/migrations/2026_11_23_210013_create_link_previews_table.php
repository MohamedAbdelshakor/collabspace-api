<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_previews', function (Blueprint $table): void {
            $table->id();
            $table->char('url_hash', 64)->unique();
            $table->text('url');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();
            $table->timestamp('fetched_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_previews');
    }
};
