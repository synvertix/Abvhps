<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            if (!Schema::hasColumn('memberships', 'identity_verified')) {
                $table->boolean('identity_verified')->default(false)->after('is_aadhaar_verified');
            }
            if (!Schema::hasColumn('memberships', 'identity_verification_method')) {
                $table->string('identity_verification_method', 30)->nullable()->after('identity_verified');
            }
            if (!Schema::hasColumn('memberships', 'identity_verification_provider')) {
                $table->string('identity_verification_provider', 30)->nullable()->after('identity_verification_method');
            }
            if (!Schema::hasColumn('memberships', 'identity_verification_id')) {
                $table->string('identity_verification_id', 50)->nullable()->after('identity_verification_provider');
            }
            if (!Schema::hasColumn('memberships', 'identity_verification_reference_id')) {
                $table->string('identity_verification_reference_id', 100)->nullable()->after('identity_verification_id');
            }
            if (!Schema::hasColumn('memberships', 'identity_verified_name')) {
                $table->string('identity_verified_name')->nullable()->after('identity_verification_reference_id');
            }
            if (!Schema::hasColumn('memberships', 'identity_document_last4')) {
                $table->string('identity_document_last4', 4)->nullable()->after('identity_verified_name');
            }
            if (!Schema::hasColumn('memberships', 'identity_verified_at')) {
                $table->timestamp('identity_verified_at')->nullable()->after('identity_document_last4');
            }
        });

        // Strict backfill for legacy Aadhaar-verified memberships only:
        // Do not fabricate provider provenance or verification timestamps:
        // identity_verification_provider remains NULL, and identity_verified_at is aadhaar_verified_at only.
        DB::table('memberships')
            ->where('is_aadhaar_verified', true)
            ->whereNotNull('full_name')
            ->where('full_name', '!=', '')
            ->update([
                'identity_verified'                  => true,
                'identity_verification_method'        => 'aadhaar',
                'identity_verification_provider'      => null,
                'identity_verification_reference_id' => DB::raw('aadhaar_verification_ref'),
                'identity_verified_name'             => DB::raw('full_name'),
                'identity_verified_at'               => DB::raw('aadhaar_verified_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $columns = [
                'identity_verified',
                'identity_verification_method',
                'identity_verification_provider',
                'identity_verification_id',
                'identity_verification_reference_id',
                'identity_verified_name',
                'identity_document_last4',
                'identity_verified_at',
            ];

            $dropCols = [];
            foreach ($columns as $col) {
                if (Schema::hasColumn('memberships', $col)) {
                    $dropCols[] = $col;
                }
            }

            if (!empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
