<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            // Nullable by design: existing history remains untouched and distinct.
            $table->timestamp('activity_window')->nullable()->after('activity_type');
            $table->unsignedInteger('activity_count')->default(1)->after('activity_window');
            $table->unique(
                ['user_id', 'product_id', 'activity_type', 'activity_window'],
                'user_activities_window_unique',
            );
            $table->index(['created_at', 'activity_id'], 'user_activities_created_index');
        });
    }

    public function down(): void
    {
        if (DB::table('user_activities')->where('activity_count', '>', 1)->exists()) {
            throw new RuntimeException(
                'Aggregated activity remains. Run activities:prepare-aggregation-rollback until it succeeds, then retry the migration rollback.'
            );
        }

        Schema::table('user_activities', function (Blueprint $table) {
            $table->dropUnique('user_activities_window_unique');
            $table->dropIndex('user_activities_created_index');
            $table->dropColumn(['activity_window', 'activity_count']);
        });
    }
};
