<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Convert old size values (1-4) to new 12-column grid values:
     * 1 -> 3 (1/4 width)
     * 2 -> 6 (1/2 width)
     * 3 -> 9 (3/4 width)
     * 4 -> 12 (full width)
     *
     * We use temporary negative values to avoid conflicts during the update.
     */
    public function up(): void
    {
        // First, convert to temporary negative values to avoid conflicts
        DB::table('user_dashboard_preferences')->where('size', 4)->update(['size' => -12]);
        DB::table('user_dashboard_preferences')->where('size', 3)->update(['size' => -9]);
        DB::table('user_dashboard_preferences')->where('size', 2)->update(['size' => -6]);
        DB::table('user_dashboard_preferences')->where('size', 1)->update(['size' => -3]);

        // Now convert negative values to positive
        DB::table('user_dashboard_preferences')->where('size', -12)->update(['size' => 12]);
        DB::table('user_dashboard_preferences')->where('size', -9)->update(['size' => 9]);
        DB::table('user_dashboard_preferences')->where('size', -6)->update(['size' => 6]);
        DB::table('user_dashboard_preferences')->where('size', -3)->update(['size' => 3]);
    }

    /**
     * Reverse the migrations.
     *
     * Convert new 12-column grid values back to old size values (1-4):
     * 3 -> 1 (1/4 width)
     * 4 -> 1 (1/3 width - maps to 1/4 since old system didn't have 1/3)
     * 6 -> 2 (1/2 width)
     * 9 -> 3 (3/4 width)
     * 12 -> 4 (full width)
     */
    public function down(): void
    {
        DB::table('user_dashboard_preferences')->where('size', 12)->update(['size' => 4]);
        DB::table('user_dashboard_preferences')->where('size', 9)->update(['size' => 3]);
        DB::table('user_dashboard_preferences')->where('size', 6)->update(['size' => 2]);
        DB::table('user_dashboard_preferences')->where('size', 4)->update(['size' => 1]);
        DB::table('user_dashboard_preferences')->where('size', 3)->update(['size' => 1]);
    }
};
