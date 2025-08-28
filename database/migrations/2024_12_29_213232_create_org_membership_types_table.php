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
        Schema::create('org_membership_types', function (Blueprint $table) {
            $table->id();

            // Keep naming consistent with org_members etc.
            $table->foreignId('org_type_user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Organisation user id');

            $table->foreignId('membership_type_id')
                ->constrained('membership_types')
                ->cascadeOnDelete()
                ->comment('Base type from global catalogue');

            // Optional validity window (useful for limited-time offerings)
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();

            // Controls
            $table->boolean('is_active')->default(true)
                ->comment('Enabled for this organisation');

            $table->boolean('is_public')->default(true)
                ->comment('Visible as a selectable option in sign-up / portals');

            $table->unsignedInteger('sort_order')->default(0)
                ->comment('UI ordering within the organisation');

            // Free-form metadata for future-proofing (labels, perks, caps, etc.)
            $table->json('meta')->nullable();


            $table->timestamps();

            // Prevent the same type being added twice for the same org
            $table->unique(['org_type_user_id', 'membership_type_id'], 'uq_org_type_membership_type');

            // Helpful index for typical queries
            $table->index(['org_type_user_id', 'is_active', 'is_public', 'sort_order'], 'idx_org_type_filters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_membership_types');
    }
};
