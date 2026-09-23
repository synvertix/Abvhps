<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_DIGITS = '8884933379';
    private const NEW_PHONE  = '+91 9989980055';
    private const NEW_DIGITS = '9989980055';

    /**
     * The only public helpline is +91 9989980055. Replace the retired number wherever a stored
     * setting still carries it (the contact_phone setting, or any text/URL that embeds it).
     */
    public function up(): void
    {
        $rows = DB::table('site_settings')->where('value', 'like', '%' . self::OLD_DIGITS . '%')->get();

        foreach ($rows as $row) {
            $value = $row->key === 'contact_phone'
                ? self::NEW_PHONE
                : str_replace(self::OLD_DIGITS, self::NEW_DIGITS, (string) $row->value);

            DB::table('site_settings')->where('id', $row->id)->update(['value' => $value, 'updated_at' => now()]);
            Cache::forget('site_setting_' . $row->key);
        }
    }

    public function down(): void
    {
        // Intentionally irreversible: the retired number must not be restored.
    }
};
