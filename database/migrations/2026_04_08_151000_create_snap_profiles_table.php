<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snap_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('display_name');
            $table->string('snap_username');
            $table->string('headline');
            $table->string('category');
            $table->text('bio');
            $table->string('looking_for');
            $table->string('location')->nullable();
            $table->boolean('is_adult')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['category', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snap_profiles');
    }
};
