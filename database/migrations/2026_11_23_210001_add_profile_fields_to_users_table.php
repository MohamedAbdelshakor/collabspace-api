<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->char('ulid', 26)->after('id')->unique();
            $table->string('password')->nullable()->change();
            $table->string('google_id')->nullable()->unique()->after('password');
            $table->string('avatar_path')->nullable()->after('google_id');
            $table->text('bio')->nullable()->after('avatar_path');
            $table->string('phone_country', 2)->nullable()->after('bio');
            $table->string('phone_number', 30)->nullable()->after('phone_country');
            $table->string('job_title', 100)->nullable()->after('phone_number');
            $table->unsignedTinyInteger('years_of_experience')->nullable()->after('job_title');
            $table->string('availability_status', 20)->default('available')->after('years_of_experience');
            $table->string('timezone', 50)->default('UTC')->after('availability_status');
            $table->string('locale', 5)->default('en')->after('timezone');
            $table->string('pending_email')->nullable()->after('locale');
            $table->timestamp('last_active_at')->nullable()->after('pending_email');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'ulid',
                'google_id',
                'avatar_path',
                'bio',
                'phone_country',
                'phone_number',
                'job_title',
                'years_of_experience',
                'availability_status',
                'timezone',
                'locale',
                'pending_email',
                'last_active_at',
            ]);
        });
    }
};
