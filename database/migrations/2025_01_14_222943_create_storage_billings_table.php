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
        Schema::create('storage_billings', function (Blueprint $table) {
            $table->id();
            // $table->string('storage_billing_code', 15)->nullable()->unique()->comment('Unique 15-character alphanumeric transaction ID with prefix AZON-STO.');
            
            // $table->foreignId('user_id')
            //     ->nullable()
            //     ->constrained('users')
            //     ->onDelete('cascade')
            //     ->comment('References the user for billing. Null if user is deleted.');
                
            // $table->string('user_name', length: 255)->nullable()->comment('User name snapshot for billing reference');

            // //Service and billing month details
            // $table->string('service_month_year', 20)->nullable()->comment('Month in which the service was consumed');
            // $table->string('billing_month_year', 20)->nullable()->comment('Month in which the bill is generated');
            // // $table->smallInteger('service_year')->nullable()->comment('Year for billing reference');
            // // $table->smallInteger('billing_year')->nullable()->comment('Year for billing reference');
 

            // //Storage billing
            // $table->string('storage_package_name', length: 15)->nullable()->comment('Storage package name on service month');
            // $table->decimal('storage_price_rate', 10, 2)->nullable()->comment('Storage rate per day');
            // $table->decimal('storage_sub_total', 10, 2)->nullable()->comment('Storage bill amount based on rate, days, and total members');
            
            //$table->integer('storage_days')->nullable()->comment('Number of days for storage');
            //$table->decimal('storage_tax', 10, 2)->default(0)->comment('Total tax applied to storage bill');
            //$table->decimal('storage_discount', 10, 2)->default(0)->comment('Storage discount amount applied to the invoice');
            //$table->decimal('storage_total', 10, 2)->nullable()->comment('Storage total bill amount including tax and discount');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_billings');
    }
};
