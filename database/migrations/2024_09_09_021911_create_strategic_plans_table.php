<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'strategic_plans' table.
     */
    public function up(): void
    {
        Schema::create('strategic_plans', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key referencing the users table (owner or creator of the strategic plan)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated strategic plans

            // Strategic plan details
            $table->string('title')->nullable(); // Title of the strategic plan
            $table->string('image')->nullable(); // image of the strategic plan

            $table->longText('plan')->nullable(); // Detailed content of the strategic plan
            $table->date('start_date')->nullable(); // Start date of the plan
            $table->date('end_date')->nullable(); // End date of the plan

            $table->foreignId('privacy_setup_id')
                ->nullable()
                ->constrained('privacy_setups')
                ->onDelete('cascade');
            // Status of the strategic plan
            $table->boolean('status')->default(1)->comment('0 = inactive, 1 = active');

            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'strategic_plans' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategic_plans');
    }
};
