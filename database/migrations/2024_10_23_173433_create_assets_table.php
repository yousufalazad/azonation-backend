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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            // Name of the asset
            $table->string('name', 100)
                ->comment('Name of the asset.');

            // Short description of the asset
            $table->string('description', 255)
                ->nullable()
                ->comment('Short description of the asset.');

            // Quantity of the asset
            $table->integer('quantity')
                ->default(1)
                ->comment('Quantity of the asset.');

            // Approximate value of the asset
            $table->decimal('value_amount', 13, 2)
                ->nullable()
                ->comment('Approximate monetary value of the asset.');

            // In-kind value (non-monetary value) of the asset
            $table->decimal('inkind_value', 13, 2)
                ->nullable()
                ->comment('In-kind value of the asset.');

            // Foreign key to the user responsible for the asset
            $table->foreignId('responsible_person_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User responsible for the asset.');

            // Date when the asset was assigned
            $table->date('assign_start_date')
                ->nullable()
                ->comment('The date the asset was assigned.');

            // Date when asset assignment ends
            $table->date('assign_end_date')
                ->nullable()
                ->comment('The date the asset assignment ends.');

            // Any additional notes regarding the asset
            $table->text('note')
                ->nullable()
                ->comment('Any additional notes about the asset.');

            // Status of the asset (e.g., active, inactive, under maintenance)
            $table->enum('current_status', [
                'draft',
                'under_maintenance',
                'disposed',
                'lost',
                'stolen',
                'damaged',
                'unknown',
                'other',
                'returned',
                'reserved',
                'in_use',
                'pending_disposal',
                'decommissioned',
                'out_for_repair',
                'in_storage',
                'awaiting_inspection',
                'transferred',
                'leased',
                'recalled',
                'quarantined',
                'expired',
                'missing',
                'being_audited',
                'repaired',
            ])
                ->default('in_use')
                ->comment('Current status of the asset.');


            //Foreign key referencing the privacy setups table (privacy settings)
            $table->foreignId('privacy_setup_id')
                ->constrained('privacy_setups')
                ->onDelete('cascade')
                ->comment('Privacy level of the asset (e.g., public, private, only members. etc).');

            $table->boolean('active_status')
                ->default(true)
                ->comment('Indicates whether the asset is currently active.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
