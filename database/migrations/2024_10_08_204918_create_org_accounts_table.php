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
        Schema::create('org_accounts', function (Blueprint $table) {
            $table->id();
            // Foreign key referencing the users table (creator or recipient of the plan)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Creator or responsible user for the transaction.');

            // Foreign key referencing the account_funds table
            $table->foreignId('account_fund_id')
            ->constrained('account_funds')
            ->onDelete('cascade')
            ->comment('Relation with fund for every transaction.');

            $table->date('transaction_date');
            
            // Enum for transaction type (income or expense)
            $table->enum('transaction_type', ['income', 'expense'])
                ->comment('Defines whether the transaction is an income or an expense.');
                
            $table->decimal('transaction_amount', 15, 2);
            $table->string('description', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_accounts');
    }
};
