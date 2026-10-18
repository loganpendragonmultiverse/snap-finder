<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_snap_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('snap_profile_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'snap_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_snap_profiles');
    }
};
