<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->string('payment_gateway')->nullable()->after('payment_id');
            $table->string('payment_order_id')->nullable()->after('payment_gateway');
            $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_order_id');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'payment_order_id',
                'payment_amount',
                'payment_verified_at',
            ]);
        });
    }
};
