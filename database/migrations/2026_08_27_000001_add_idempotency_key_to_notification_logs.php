<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_logs')) {
            return;
        }

        // 1. Ensure idempotency_key column exists
        if (!Schema::hasColumn('notification_logs', 'idempotency_key')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->string('idempotency_key', 191)->nullable()->after('id');
            });
        }

        // 2. Ensure explicit unique index notification_logs_idempotency_key_unique exists
        $indexes = Schema::getIndexes('notification_logs');
        $idempotencyIndex = collect($indexes)->firstWhere('name', 'notification_logs_idempotency_key_unique');

        if ($idempotencyIndex) {
            // Verify it is unique and covers only idempotency_key
            if (!$idempotencyIndex['unique'] || $idempotencyIndex['columns'] !== ['idempotency_key']) {
                throw new \RuntimeException(
                    "Existing index 'notification_logs_idempotency_key_unique' is invalid (unique=" .
                    ($idempotencyIndex['unique'] ? 'true' : 'false') . ", columns=" .
                    implode(',', $idempotencyIndex['columns']) . ")."
                );
            }
        } else {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->unique('idempotency_key', 'notification_logs_idempotency_key_unique');
            });
        }

        // 3. Drop obsolete composite unique constraint if present
        $indexes = Schema::getIndexes('notification_logs');
        $oldIndex = collect($indexes)->firstWhere('name', 'notif_logs_idempotency_idx');

        if ($oldIndex && $oldIndex['unique']) {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->dropUnique('notif_logs_idempotency_idx');
            });
        }

        // 4. Ensure non-unique lookup index notif_logs_lookup_idx exists
        $expectedLookupColumns = ['event_type', 'notifiable_type', 'notifiable_id', 'channel'];
        $indexes = Schema::getIndexes('notification_logs');
        $lookupIndex = collect($indexes)->firstWhere('name', 'notif_logs_lookup_idx');

        if ($lookupIndex) {
            if ($lookupIndex['unique'] || $lookupIndex['columns'] !== $expectedLookupColumns) {
                throw new \RuntimeException(
                    "Existing index 'notif_logs_lookup_idx' is incompatible (unique=" .
                    ($lookupIndex['unique'] ? 'true' : 'false') . ", columns=" .
                    implode(',', $lookupIndex['columns']) . ")."
                );
            }
        } else {
            Schema::table('notification_logs', function (Blueprint $table) use ($expectedLookupColumns) {
                $table->index($expectedLookupColumns, 'notif_logs_lookup_idx');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('notification_logs')) {
            return;
        }

        $indexes = Schema::getIndexes('notification_logs');

        // 1. Drop lookup index if present
        if (collect($indexes)->contains('name', 'notif_logs_lookup_idx')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->dropIndex('notif_logs_lookup_idx');
            });
        }

        // 2. Drop unique idempotency key index if present
        if (collect($indexes)->contains('name', 'notification_logs_idempotency_key_unique')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->dropUnique('notification_logs_idempotency_key_unique');
            });
        }

        // 3. Drop column if present
        if (Schema::hasColumn('notification_logs', 'idempotency_key')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->dropColumn('idempotency_key');
            });
        }
    }
};
