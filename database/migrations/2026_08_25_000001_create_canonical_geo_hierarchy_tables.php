<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for the 5-tier canonical geographic hierarchy below National scope.
     */
    public function up(): void
    {
        // 1. Tier 1: Canonical States
        if (!Schema::hasTable('geo_states')) {
            Schema::create('geo_states', function (Blueprint $table) {
                $table->id();
                $table->string('country', 100)->default('India');
                $table->string('name', 100);
                $table->string('code', 20)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['country', 'name'], 'geo_states_country_name_unique');
                $table->index(['is_active', 'name']);
            });
        }

        // 2. Tier 2: Canonical Districts
        if (!Schema::hasTable('geo_districts')) {
            Schema::create('geo_districts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->constrained('geo_states')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('code', 20)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['state_id', 'name'], 'geo_districts_state_name_unique');
                $table->index(['state_id', 'is_active']);
            });
        }

        // 3. Tier 3: Canonical Assembly Segments / Talukas
        if (!Schema::hasTable('geo_assembly_segments')) {
            Schema::create('geo_assembly_segments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('district_id')->constrained('geo_districts')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('code', 20)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['district_id', 'name'], 'geo_assembly_dist_name_unique');
                $table->index(['district_id', 'is_active']);
            });
        }

        // 4. Tier 4: Canonical Mandals
        if (!Schema::hasTable('geo_mandals')) {
            Schema::create('geo_mandals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('district_id')->constrained('geo_districts')->cascadeOnDelete();
                $table->foreignId('assembly_segment_id')->nullable()->constrained('geo_assembly_segments')->nullOnDelete();
                $table->string('name', 100);
                $table->string('code', 20)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['district_id', 'name'], 'geo_mandals_dist_name_unique');
                $table->index(['district_id', 'assembly_segment_id']);
                $table->index(['assembly_segment_id', 'is_active']);
            });
        }

        // 5. Tier 5: Canonical Grama Panchayats
        if (!Schema::hasTable('geo_panchayats')) {
            Schema::create('geo_panchayats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mandal_id')->constrained('geo_mandals')->cascadeOnDelete();
                $table->string('name', 150);
                $table->string('pincode', 10)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['mandal_id', 'name'], 'geo_panchayats_mandal_name_unique');
                $table->index(['mandal_id', 'is_active']);
                $table->index(['name']);
            });
        }

        // 6. Auxiliary Table: Approved Geographic Aliases
        if (!Schema::hasTable('geo_aliases')) {
            Schema::create('geo_aliases', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 50); // state, district, assembly_segment, mandal, panchayat
                $table->string('alias_name', 150);
                $table->unsignedBigInteger('canonical_id');
                $table->foreignId('state_id')->nullable()->constrained('geo_states')->nullOnDelete();
                $table->foreignId('district_id')->nullable()->constrained('geo_districts')->nullOnDelete();
                $table->unsignedBigInteger('approved_by_admin_id')->nullable();
                $table->timestamps();

                $table->unique(['entity_type', 'alias_name', 'state_id', 'district_id'], 'geo_aliases_lookup_unique');
                $table->index(['entity_type', 'alias_name']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_aliases');
        Schema::dropIfExists('geo_panchayats');
        Schema::dropIfExists('geo_mandals');
        Schema::dropIfExists('geo_assembly_segments');
        Schema::dropIfExists('geo_districts');
        Schema::dropIfExists('geo_states');
    }
};
