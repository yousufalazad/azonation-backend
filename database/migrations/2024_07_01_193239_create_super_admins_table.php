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
        Schema::create('super_admins', function (Blueprint $table) {
            $table->id();

            // Foreign key to users table
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('Reference to the user associated with the super admin');

            // Custom auto-incrementing column, like Azon ID
            $table->bigInteger('azon_id')
                  ->nullable()
                  ->unique()
                  ->comment('Custom unique identifier for the super admin');

            // Name of the super admin
            $table->string('admin_name')
                  ->nullable()
                  ->comment('Name of the super admin');

            // Short description of the super admin
            $table->string('short_description')
                  ->nullable()
                  ->comment('Short description of the super admin');

            // Additional notes
            $table->string('note')
                  ->nullable()
                  ->comment('Any additional notes related to the super admin');

            // Supervision details
            $table->string('supervision')
                  ->nullable()
                  ->comment('Details about supervision responsibilities');

            // Start date of supervision
            $table->date('start_date')
                  ->nullable()
                  ->comment('Start date of the super admin role');

            // End date of supervision
            $table->date('end_date')
                  ->nullable()
                  ->comment('End date of the super admin role');

            // Status of the super admin (e.g., active, inactive)
            $table->tinyInteger('status')
                  ->nullable()
                  ->default(0)
                  ->comment('Status of the super admin (0=inactive, 1=active)');

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_admins');
    }
};
