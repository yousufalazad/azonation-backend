<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration creates the 'packages' table, defining different subscription plans for Azonation.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id(); 
            $table->string('name'); // Name of the package (e.g., Starter, Essentials, Professional, Enterprise)
            $table->string('slug')->unique(); // Unique slug for URL and identification purposes
            $table->text('description')->nullable(); // Optional description of the package
            $table->integer('max_members')->default(10); // Maximum number of active members allowed in the package
            $table->integer('storage_limit')->default(500); // Storage limit in MB (default 500MB for Starter plan)

            // Features toggled by boolean values (defaulting to false for lower-tier packages)
            $table->boolean('custom_branding')->default(false); // Allow custom branding (logo, colors)
            $table->boolean('api_access')->default(false); // API access enabled for advanced users
            $table->boolean('priority_support')->default(false); // Priority support for premium plans
            $table->integer('meeting_limit')->default(10); // Max number of meetings allowed per month
            $table->integer('event_limit')->default(10); // Max number of events allowed per month
            $table->integer('project_limit')->default(5); // Max number of projects allowed in the system
            $table->integer('office_record_limit')->default(10); // Max number of office records allowed
            $table->boolean('report')->default(true); // Basic reporting enabled for all packages
            $table->boolean('advanced_report')->default(false); // Advanced reporting tools
            $table->boolean('custom_report')->default(false); // Custom reporting options

            // Support levels
            $table->boolean('support')->default(true); // Basic email support
            $table->boolean('premium_support')->default(false); // Premium support (live chat, etc.)
            $table->boolean('dedicated_account_manager')->default(false); // Dedicated account manager for large clients

            // Customization features
            $table->boolean('custom_domain')->default(false); // Support for custom domains
            $table->boolean('custom_email_template')->default(false); // Custom email templates

            // Multi-currency payment support
            $table->boolean('multi_currency_payment')->default(false); // Enable multi-currency payments for different regions
            
            // Username creation feature (Exclusive for Enterprise)
            $table->boolean('username_creation')->default(false); // Enable creating a custom username for enterprise package

            // Add web link option for package listing
            $table->string('web_link')->nullable(); // Optional web link to the package page for additional info

            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'packages' table if necessary.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
