<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add nullable canonical FKs and cadre fields to volunteers & memberships.
     */
    public function up(): void
    {
        // 1. Update Volunteers Table
        if (Schema::hasTable('volunteers')) {
            Schema::table('volunteers', function (Blueprint $table) {
                if (!Schema::hasColumn('volunteers', 'state_id')) {
                    $table->foreignId('state_id')->nullable()->after('locality')->constrained('geo_states')->nullOnDelete();
                }
                if (!Schema::hasColumn('volunteers', 'district_id')) {
                    $table->foreignId('district_id')->nullable()->after('state_id')->constrained('geo_districts')->nullOnDelete();
                }
                if (!Schema::hasColumn('volunteers', 'assembly_segment_id')) {
                    $table->foreignId('assembly_segment_id')->nullable()->after('district_id')->constrained('geo_assembly_segments')->nullOnDelete();
                }
                if (!Schema::hasColumn('volunteers', 'mandal_id')) {
                    $table->foreignId('mandal_id')->nullable()->after('assembly_segment_id')->constrained('geo_mandals')->nullOnDelete();
                }
                if (!Schema::hasColumn('volunteers', 'panchayat_id')) {
                    $table->foreignId('panchayat_id')->nullable()->after('mandal_id')->constrained('geo_panchayats')->nullOnDelete();
                }
                if (!Schema::hasColumn('volunteers', 'cadre_level')) {
                    $table->string('cadre_level', 50)->nullable()->after('cadre');
                }
                if (!Schema::hasColumn('volunteers', 'geo_mapping_status')) {
                    $table->string('geo_mapping_status', 50)->default('unmapped')->after('panchayat_id');
                }
                if (!Schema::hasColumn('volunteers', 'geo_mapping_notes')) {
                    $table->text('geo_mapping_notes')->nullable()->after('geo_mapping_status');
                }
            });

            // Add indexes in a safe manner
            try {
                Schema::table('volunteers', function (Blueprint $table) {
                    $table->index(['cadre_level', 'status'], 'vol_cadre_status_idx');
                });
            } catch (\Throwable $e) {
                // Index may already exist if retrying a partial migration
            }

            try {
                Schema::table('volunteers', function (Blueprint $table) {
                    $table->index(['geo_mapping_status'], 'vol_geo_mapping_status_idx');
                });
            } catch (\Throwable $e) {
                // Index may already exist
            }
        }

        // 2. Update Memberships Table
        if (Schema::hasTable('memberships')) {
            Schema::table('memberships', function (Blueprint $table) {
                if (!Schema::hasColumn('memberships', 'state_id')) {
                    $table->foreignId('state_id')->nullable()->after('country')->constrained('geo_states')->nullOnDelete();
                }
                if (!Schema::hasColumn('memberships', 'district_id')) {
                    $table->foreignId('district_id')->nullable()->after('state_id')->constrained('geo_districts')->nullOnDelete();
                }
                if (!Schema::hasColumn('memberships', 'assembly_segment_id')) {
                    $table->foreignId('assembly_segment_id')->nullable()->after('district_id')->constrained('geo_assembly_segments')->nullOnDelete();
                }
                if (!Schema::hasColumn('memberships', 'mandal_id')) {
                    $table->foreignId('mandal_id')->nullable()->after('assembly_segment_id')->constrained('geo_mandals')->nullOnDelete();
                }
                if (!Schema::hasColumn('memberships', 'panchayat_id')) {
                    $table->foreignId('panchayat_id')->nullable()->after('mandal_id')->constrained('geo_panchayats')->nullOnDelete();
                }
                if (!Schema::hasColumn('memberships', 'geo_mapping_status')) {
                    $table->string('geo_mapping_status', 50)->default('unmapped')->after('panchayat_id');
                }
                if (!Schema::hasColumn('memberships', 'geo_mapping_notes')) {
                    $table->text('geo_mapping_notes')->nullable()->after('geo_mapping_status');
                }
            });

            // Add indexes in a safe manner
            try {
                Schema::table('memberships', function (Blueprint $table) {
                    $table->index(['state_id', 'district_id', 'mandal_id', 'panchayat_id'], 'memb_geo_fks_idx');
                });
            } catch (\Throwable $e) {
                // Index may already exist
            }

            try {
                Schema::table('memberships', function (Blueprint $table) {
                    $table->index(['geo_mapping_status'], 'memb_geo_mapping_status_idx');
                });
            } catch (\Throwable $e) {
                // Index may already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('volunteers')) {
            Schema::table('volunteers', function (Blueprint $table) {
                try { $table->dropForeign(['state_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['district_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['assembly_segment_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['mandal_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['panchayat_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex('vol_cadre_status_idx'); } catch (\Throwable $e) {}
                try { $table->dropIndex('vol_geo_mapping_status_idx'); } catch (\Throwable $e) {}

                $columnsToDrop = [];
                foreach (['state_id', 'district_id', 'assembly_segment_id', 'mandal_id', 'panchayat_id', 'cadre_level', 'geo_mapping_status', 'geo_mapping_notes'] as $col) {
                    if (Schema::hasColumn('volunteers', $col)) {
                        $columnsToDrop[] = $col;
                    }
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }

        if (Schema::hasTable('memberships')) {
            Schema::table('memberships', function (Blueprint $table) {
                try { $table->dropForeign(['state_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['district_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['assembly_segment_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['mandal_id']); } catch (\Throwable $e) {}
                try { $table->dropForeign(['panchayat_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex('memb_geo_fks_idx'); } catch (\Throwable $e) {}
                try { $table->dropIndex('memb_geo_mapping_status_idx'); } catch (\Throwable $e) {}

                $columnsToDrop = [];
                foreach (['state_id', 'district_id', 'assembly_segment_id', 'mandal_id', 'panchayat_id', 'geo_mapping_status', 'geo_mapping_notes'] as $col) {
                    if (Schema::hasColumn('memberships', $col)) {
                        $columnsToDrop[] = $col;
                    }
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
