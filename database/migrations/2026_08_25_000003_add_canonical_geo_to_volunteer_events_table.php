<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add nullable canonical geo foreign keys to volunteer_events.
     */
    public function up(): void
    {
        if (Schema::hasTable('volunteer_events')) {
            Schema::table('volunteer_events', function (Blueprint $table) {
                if (!Schema::hasColumn('volunteer_events', 'state_id')) {
                    $table->foreignId('state_id')->nullable()->after('state')->constrained('geo_states')->nullOnDelete();
                }
                if (!Schema::hasColumn('volunteer_events', 'district_id')) {
                    $table->foreignId('district_id')->nullable()->after('state_id')->constrained('geo_districts')->nullOnDelete();
                }
                if (!Schema::hasColumn('volunteer_events', 'assembly_segment_id')) {
                    $table->foreignId('assembly_segment_id')->nullable()->after('district_id')->constrained('geo_assembly_segments')->nullOnDelete();
                }
                if (!Schema::hasColumn('volunteer_events', 'mandal_id')) {
                    $table->foreignId('mandal_id')->nullable()->after('assembly_segment_id')->constrained('geo_mandals')->nullOnDelete();
                }
                if (!Schema::hasColumn('volunteer_events', 'panchayat_id')) {
                    $table->foreignId('panchayat_id')->nullable()->after('mandal_id')->constrained('geo_panchayats')->nullOnDelete();
                }
            });

            try {
                Schema::table('volunteer_events', function (Blueprint $table) {
                    $table->index(['state_id'], 've_state_id_idx');
                    $table->index(['district_id'], 've_district_id_idx');
                    $table->index(['assembly_segment_id'], 've_assembly_id_idx');
                    $table->index(['mandal_id'], 've_mandal_id_idx');
                    $table->index(['panchayat_id'], 've_panchayat_id_idx');
                });
            } catch (\Throwable $e) {
                // Ignore if index already exists
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('volunteer_events')) {
            Schema::table('volunteer_events', function (Blueprint $table) {
                $columns = ['panchayat_id', 'mandal_id', 'assembly_segment_id', 'district_id', 'state_id'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('volunteer_events', $column)) {
                        try {
                            $table->dropForeign(['volunteer_events_' . $column . '_foreign']);
                        } catch (\Throwable $e) {
                            // Foreign key might have default name
                        }
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
