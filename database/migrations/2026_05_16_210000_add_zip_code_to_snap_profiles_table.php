<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('snap_profiles', 'zip_code')) {
            return;
        }

        Schema::table('snap_profiles', function (Blueprint $table) {
            $table->string('zip_code', 10)->nullable()->after('location');
            $table->index('zip_code');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('snap_profiles', 'zip_code')) {
            return;
        }

        Schema::table('snap_profiles', function (Blueprint $table) {
            $table->dropIndex(['zip_code']);
            $table->dropColumn('zip_code');
        });
    }
};
