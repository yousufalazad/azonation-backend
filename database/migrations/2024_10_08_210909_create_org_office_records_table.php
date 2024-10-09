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
        Schema::create('org_office_records', function (Blueprint $table) {
            $table->id();
            // Foreign key referencing the users table (creator or recipient of the plan)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Creator or responsible user for the transaction.');

            $table->string('title', 255);
            $table->string('description', 255);
            $table->string('document', 255);

            // Foreign key referencing the privacy setups table (privacy settings)
            $table->foreignId('privacy_setup_id')
            ->constrained('privacy_setups')
            ->onDelete('cascade')
            ->comment('Privacy level of the year plan (e.g., public, private, only members).');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_office_records');
    }
};
