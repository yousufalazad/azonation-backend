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
        Schema::create('accounts_transaction_currencies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Reference to the user associated with Organizations user ID'); // Organization's user ID

            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->cascadeOnDelete()
                ->comment('Foreign key referencing the currencies table, used for the organisation accounts transaction (not for billing)'); // The currency used for the organisation accounts transaction (not for billing)

            $table->boolean(column: 'is_active')->default(value: true); // Indicates whether the transaction currency is active or not
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts_transaction_currencies');
    }
};
