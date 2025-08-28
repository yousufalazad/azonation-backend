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
        Schema::create('membership_termination_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('reason', 100)->unique()->comment('Reason for membership termination');
            $table->string('description', 255)->nullable()->comment('Detailed description of the reason');
            $table->boolean('is_active')->default(true)->comment('Whether this reason is active/usable or inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_termination_reasons');
    }
};
