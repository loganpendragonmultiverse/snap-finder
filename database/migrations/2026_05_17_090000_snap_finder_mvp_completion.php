<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_admin');
            }
            if (! Schema::hasColumn('users', 'is_suspended')) {
                $table->boolean('is_suspended')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('users', 'age_verified_at')) {
                $table->timestamp('age_verified_at')->nullable()->after('email_verified_at');
            }
            if (! Schema::hasColumn('users', 'age_verification_method')) {
                $table->string('age_verification_method')->nullable()->after('age_verified_at');
            }
            if (! Schema::hasColumn('users', 'age_verification_meta')) {
                $table->json('age_verification_meta')->nullable()->after('age_verification_method');
            }
        });

        Schema::table('snap_profiles', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (Throwable $exception) {
                //
            }

            try {
                $table->dropUnique('snap_profiles_user_id_unique');
            } catch (Throwable $exception) {
                //
            }

            if (! Schema::hasColumn('snap_profiles', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('zip_code');
            }
            if (! Schema::hasColumn('snap_profiles', 'status')) {
                $table->string('status')->default('pending')->after('is_featured');
            }
            if (! Schema::hasColumn('snap_profiles', 'status_reason')) {
                $table->text('status_reason')->nullable()->after('status');
            }
            if (! Schema::hasColumn('snap_profiles', 'paused_at')) {
                $table->timestamp('paused_at')->nullable()->after('status_reason');
            }
        });

        Schema::table('snap_profiles', function (Blueprint $table) {
            try {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            } catch (Throwable $exception) {
                //
            }
        });

        if (! Schema::hasTable('snap_categories')) {
            Schema::create('snap_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->boolean('is_nsfw')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('snap_tags')) {
            Schema::create('snap_tags', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->boolean('is_nsfw')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('snap_category_snap_profile')) {
            Schema::create('snap_category_snap_profile', function (Blueprint $table) {
                $table->foreignId('snap_profile_id')->constrained('snap_profiles')->cascadeOnDelete();
                $table->foreignId('snap_category_id')->constrained('snap_categories')->cascadeOnDelete();
                $table->primary(['snap_profile_id', 'snap_category_id'], 'snap_profile_category_primary');
            });
        }

        if (! Schema::hasTable('snap_profile_snap_tag')) {
            Schema::create('snap_profile_snap_tag', function (Blueprint $table) {
                $table->foreignId('snap_profile_id')->constrained('snap_profiles')->cascadeOnDelete();
                $table->foreignId('snap_tag_id')->constrained('snap_tags')->cascadeOnDelete();
                $table->primary(['snap_profile_id', 'snap_tag_id'], 'snap_profile_tag_primary');
            });
        }

        if (! Schema::hasTable('snap_profile_reports')) {
            Schema::create('snap_profile_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('snap_profile_id')->constrained('snap_profiles')->cascadeOnDelete();
                $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reason');
                $table->text('details')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }

        DB::table('snap_profiles')
            ->where('is_approved', true)
            ->update(['status' => 'approved']);

        DB::table('snap_profiles')
            ->where('is_approved', false)
            ->update(['status' => 'pending']);
    }

    public function down(): void
    {
        Schema::dropIfExists('snap_profile_reports');
        Schema::dropIfExists('snap_profile_snap_tag');
        Schema::dropIfExists('snap_category_snap_profile');
        Schema::dropIfExists('snap_tags');
        Schema::dropIfExists('snap_categories');

        Schema::table('snap_profiles', function (Blueprint $table) {
            foreach (['profile_image', 'status', 'status_reason', 'paused_at'] as $column) {
                if (Schema::hasColumn('snap_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['is_suspended', 'age_verified_at', 'age_verification_method', 'age_verification_meta'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
