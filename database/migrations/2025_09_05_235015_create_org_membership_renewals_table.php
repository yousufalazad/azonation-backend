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
        Schema::create('org_membership_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_type_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Reference to the users for organisation');

            $table->foreignId('individual_type_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Reference to the users for organisation member');

            // What cycle & rule were applied at the time (snapshots by FK)
            $table->foreignId('membership_renewal_cycle_id')
                ->nullable()
                ->constrained('membership_renewal_cycles')
                ->onDelete('cascade')
                ->comment('Reference to the membership renewal cycle');

            // Coverage window (store UTC, compute in org TZ)
            $table->timestamp('period_start')->nullable()->comment('Start of covered period (UTC)');
            $table->timestamp('period_end')->nullable()->comment('End of covered period (UTC)');
            $table->decimal('amount_paid')->nullable()->comment('Paid amount');
            // Process state
            $table->enum('status', [
                'completed',
                'pending',
                'scheduled',
                'processing',
                'requires_action',
                'failed',
                'cancelled',
                'in_review',
                'on_hold'
            ])->default('pending')->index();

            $table->enum('initiated_by', ['system','individual', 'organisation', 'superadmin'])->default('organisation');
            $table->string('initiated_source', 20)->default('web'); // web|cron|api|webhook

            // Scheduling / retry telemetry
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
                
            // Outcome timestamps
            $table->timestamp('renewed_at')->nullable()
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

            $table->timestamps();

            // Guards & query helpers
            $table->unique(['individual_type_user_id', 'period_start', 'period_end'], 'uq_orgmember_periodstartend');
            $table->index(['individual_type_user_id', 'status', 'period_end'], 'idx_member_status_periodend');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_membership_renewals');
    }
};
