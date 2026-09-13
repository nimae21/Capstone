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
        // Preserve recommendation strength if this migration is intentionally
        // rolled back after aggregated events have already been recorded.
        DB::table('user_activities')
            ->where('activity_count', '>', 1)
            ->orderBy('activity_id')
            ->chunkById(100, function ($activities) {
                $copies = [];

                foreach ($activities as $activity) {
                    for ($copy = 1; $copy < $activity->activity_count; $copy++) {
                        $copies[] = [
                            'user_id' => $activity->user_id,
                            'product_id' => $activity->product_id,
                            'activity_type' => $activity->activity_type,
                            'activity_window' => null,
                            'activity_count' => 1,
                            'created_at' => $activity->created_at,
                            'updated_at' => $activity->updated_at,
                        ];

                        if (count($copies) === 500) {
                            DB::table('user_activities')->insert($copies);
                            $copies = [];
                        }
                    }
                }

                if ($copies !== []) {
                    DB::table('user_activities')->insert($copies);
                }
            }, 'activity_id');

        Schema::table('user_activities', function (Blueprint $table) {
            $table->dropUnique('user_activities_window_unique');
            $table->dropIndex('user_activities_created_index');
            $table->dropColumn(['activity_window', 'activity_count']);
        });
    }
};
