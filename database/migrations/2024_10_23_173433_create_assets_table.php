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

            // Foreign key to the 'users' table
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Org user for the asset.');

            // Name of the asset
            $table->string('name', 100)
                ->comment('Name of the asset.');

            // Short description of the asset
            $table->string('description', 255)
                ->nullable()
                ->comment('Short description of the asset.');

            // Type of the asset
            $table->boolean('is_long_term')
                ->nullable()
                ->default(null)
                ->comment('Indicates if the asset is long-term (true) or short-term (false).');

            // Quantity of the asset
            $table->integer('quantity')
                ->default(1)
                ->comment('Quantity of the asset.');

            // Approximate value of the asset
            $table->decimal('value_amount', 15, 2)
                ->nullable()
                ->comment('Approximate monetary value of the asset.');

            // In-kind value (non-monetary value) of the asset
            $table->decimal('inkind_value', 15, 2)
                ->nullable()
                ->comment('In-kind value of the asset.');

            // Weather asset is tangible asset
            $table->boolean('is_tangible')
                ->nullable()
                ->default(null)
                ->comment('Indicates if the asset is tangible (true) or intangible (false).');
            
            //Foreign key referencing the privacy setups table (privacy settings)
            $table->foreignId('privacy_setup_id')
                ->constrained('privacy_setups')
                ->onDelete('cascade')
                ->comment('Privacy level of the asset (e.g., public, private, only members. etc).');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Indicates whether the asset is currently active.');

            $table->timestamps();


            // // Status of the asset end of the assignment    
            // $table->enum('asset_lifecycle_statuses_id', [
            //     'draft',
            //     'under_maintenance',
            //     'disposed',
            //     'lost',
            //     'stolen',
            //     'damaged',
            //     'unknown',
            //     'other',
            //     'returned',
            //     'reserved',
            //     'in_use',
            //     'pending_disposal',
            //     'decommissioned',
            //     'out_for_repair',
            //     'in_storage',
            //     'awaiting_inspection',
            //     'transferred',
            //     'leased',
            //     'recalled',
            //     'quarantined',
            //     'expired',
            //     'missing',
            //     'being_audited',
            //     'repaired',
            // ])
            //     ->default('in_use')
            //     ->comment('Current status of the asset.');
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
