<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional call-to-action button per hero slide (label + link).
     */
    public function up(): void
    {
        Schema::table('home_sliders', function (Blueprint $table) {
            if (!Schema::hasColumn('home_sliders', 'cta_label')) {
                $table->string('cta_label', 60)->nullable()->after('subtitle');
            }
            if (!Schema::hasColumn('home_sliders', 'cta_url')) {
                $table->string('cta_url', 255)->nullable()->after('cta_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('home_sliders', function (Blueprint $table) {
            foreach (['cta_url', 'cta_label'] as $column) {
                if (Schema::hasColumn('home_sliders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
