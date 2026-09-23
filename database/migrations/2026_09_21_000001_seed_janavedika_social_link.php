<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const KEY = 'social_janavedika_url';
    private const URL = 'https://janavedika.in/@abvhps';

    /**
     * Seed the official ABVHPS Janavedika page, only when no value is configured yet.
     */
    public function up(): void
    {
        $existing = DB::table('site_settings')->where('key', self::KEY)->first();

        if ($existing === null) {
            DB::table('site_settings')->insert([
                'key'        => self::KEY,
                'value'      => self::URL,
                'group'      => 'general',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } elseif (trim((string) $existing->value) === '') {
            DB::table('site_settings')->where('key', self::KEY)->update(['value' => self::URL, 'updated_at' => now()]);
        }

        Cache::forget('site_setting_' . self::KEY);
    }

    public function down(): void
    {
        DB::table('site_settings')->where('key', self::KEY)->where('value', self::URL)->delete();
        Cache::forget('site_setting_' . self::KEY);
    }
};
