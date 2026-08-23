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
        Schema::create('volunteer_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volunteer_id')->constrained('volunteers')->cascadeOnDelete();
            $table->string('title');
            $table->string('event_type');
            $table->text('description')->nullable();
            $table->date('event_date')->index();
            $table->string('start_time', 20)->nullable();
            $table->string('end_time', 20)->nullable();
            $table->string('venue')->nullable();
            $table->string('village')->nullable();
            $table->string('mandal')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('status', 30)->default('upcoming')->index();
            $table->text('outcome')->nullable();
            $table->timestamps();

            $table->index(['volunteer_id', 'status']);
            $table->index(['volunteer_id', 'event_date']);
        });

        Schema::create('volunteer_event_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volunteer_event_id')->constrained('volunteer_events')->cascadeOnDelete();
            $table->foreignId('membership_record_id')->constrained('memberships')->cascadeOnDelete();
            $table->string('membership_id', 12)->index();
            $table->string('participation_type', 50)->default('participant')->index();
            $table->string('participation_status', 50)->default('participated')->index();
            $table->text('benefit_details')->nullable();
            $table->text('notes')->nullable();
            $table->string('proof_image_path')->nullable();
            $table->unsignedInteger('proof_image_size_bytes')->nullable();
            $table->string('proof_image_mime', 50)->nullable();
            $table->unsignedSmallInteger('proof_image_width')->nullable();
            $table->unsignedSmallInteger('proof_image_height')->nullable();
            $table->foreignId('added_by_volunteer_id')->constrained('volunteers')->cascadeOnDelete();
            $table->timestamps();

            // Canonical relational uniqueness
            $table->unique(['volunteer_event_id', 'membership_record_id'], 'vem_event_member_unique');
            $table->index(['volunteer_event_id', 'membership_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteer_event_members');
        Schema::dropIfExists('volunteer_events');
    }
};
