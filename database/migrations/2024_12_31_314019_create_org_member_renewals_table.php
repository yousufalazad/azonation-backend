<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('org_member_renewals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('org_member_id')
                ->constrained('org_members')
                ->cascadeOnDelete()
                ->comment('Organisation member');

            // What cycle & rule were applied at the time (snapshots by FK)
            $table->foreignId('membership_renewal_cycle_id')
                ->nullable()
                ->constrained('membership_renewal_cycles')
                ->nullOnDelete();
                
            $table->foreignId('org_membership_renewal_cycle_id')
                ->nullable()
                ->constrained('org_membership_renewal_cycles')
                ->nullOnDelete();

            // Coverage window (store UTC, compute in org TZ)
            $table->timestampTz('period_start')->comment('Start of covered period (UTC)');
            $table->timestampTz('period_end')->comment('End of covered period (UTC)');

            // Process state
            $table->enum('status', [
                'pending',
                'scheduled',
                'processing',
                'requires_action',
                'completed',
                'failed',
                'cancelled',
                'in_review',
                'on_hold'
            ])->default('pending')->index();

            $table->enum('initiated_by', ['system', 'user', 'organisation'])->default('system');
            $table->string('initiated_source', 20)->default('cron'); // web|cron|api|webhook

            $table->boolean('is_auto_renewal')->default(false);

            // Scheduling / retry telemetry
            $table->timestampTz('scheduled_for')->nullable();
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestampTz('last_attempt_at')->nullable();
                
            // Outcome timestamps
            $table->timestampTz('renewed_at')->nullable()
                ->comment('When renewal took effect/was activated');

            // Optional linkage to billing domain (kept nullable since you manage them elsewhere)
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();

            // Failure diagnostics
            $table->string('failure_code', 50)->nullable();         // e.g. card_declined, no_price
            $table->string('failure_message', 255)->nullable();     // human-readable detail

            // Notes & metadata
            $table->string('org_notes', 255)->nullable();
            $table->uuid('idempotency_key')->nullable()->unique();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Guards & query helpers
            $table->unique(['org_member_id', 'period_start'], 'uq_orgmember_periodstart');
            $table->index(['org_member_id', 'status', 'period_end'], 'idx_member_status_end');
            $table->index(['scheduled_for'], 'idx_scheduled_for');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('org_member_renewals');
    }
};
