<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * "under the behest of" -> "under the guidance of" wherever a stored setting still carries the old wording.
     */
    public function up(): void
    {
        $rows = DB::table('site_settings')->where('value', 'like', '%under the behest of%')->get();

        foreach ($rows as $row) {
            DB::table('site_settings')->where('id', $row->id)->update([
                'value'      => str_replace('under the behest of', 'under the guidance of', (string) $row->value),
                'updated_at' => now(),
            ]);
            Cache::forget('site_setting_' . $row->key);
        }
    }

    public function down(): void
    {
        // Wording fix only; nothing to restore.
    }
};
