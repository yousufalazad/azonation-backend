<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Invoices are built from monthly bills: their lines are services, not shop products,
// and payments are often made by bank transfer or cash and recorded by the Super Admin.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE order_items MODIFY product_id BIGINT UNSIGNED NULL');
        DB::statement("ALTER TABLE receipts MODIFY gateway_type ENUM('stripe','paypal','sslcommerze','bkash','rocket','upi','alipay','applepay','gpay','bank_transfer','cash','cheque','card','other') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE receipts MODIFY gateway_type ENUM('stripe','paypal','sslcommerze','bkash','rocket','upi','alipay','applepay','gpay') NOT NULL");
        DB::statement('ALTER TABLE order_items MODIFY product_id BIGINT UNSIGNED NOT NULL');
    }
};
