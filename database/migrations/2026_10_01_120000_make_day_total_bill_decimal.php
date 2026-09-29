<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// A day's bill is members × a small daily price (e.g. 8 × 0.03 = 0.24).
// It was stored as a whole number, so small bills were rounded to 0 or 1.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE everyday_member_count_and_billings MODIFY day_total_bill DECIMAL(12,4) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE everyday_member_count_and_billings MODIFY day_total_bill INT NOT NULL DEFAULT 0');
    }
};
