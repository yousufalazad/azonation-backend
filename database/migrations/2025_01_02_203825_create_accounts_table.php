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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            // Unique alphanumeric identifier for each transaction
            $table->string('transaction_code', 15)
                ->unique()
                ->comment('Unique 13-character alphanumeric transaction ID with prefix T.');
            
            // Foreign key referencing the users table (creator or responsible user)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Reference to the user associated with the transaction.');
                
            // Title of the transaction
            $table->string('transaction_title', 100)
                ->comment('Descriptive title of the transaction.');
            
            // Brief description of the transaction
            $table->string('description', 255)
            ->nullable()
            ->comment('Optional detailed description of the transaction.');
                
            // Foreign key referencing the account_funds table
            $table->foreignId('accounts_fund_id')
                ->constrained('accounts_funds')
                ->onDelete('cascade')
                ->comment('Reference to the specific fund associated with the transaction.');
                
            // Transaction date
            $table->date('date')
                ->comment('The date the transaction occurred.');
                
            // Type of transaction (income or expense)
            $table->enum('type', ['income', 'expense'])
                ->comment('Specifies if the transaction is income or expense.');
                
            // Amount involved in the transaction
            $table->decimal('amount', 15, 2)
                ->comment('Total amount of the transaction.');

            // Transaction status: 1 = Active, 0 = Inactive
            $table->boolean('is_active')
            ->default(1)
            ->comment('is_active: 1 = Active, 0 = Inactive.');
                  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
