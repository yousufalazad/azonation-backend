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
            $table->id()->comment('Primary key for the packages table');
            $table->string('name', 30)->comment('Name of the package (e.g., Starter, Essentials, Professional, Enterprise)');
            $table->string('slug', 100)->unique()->comment('Unique slug for URL and identification purposes');
            $table->string('description', 255)->nullable()->comment('Optional description of the package');
            $table->integer('max_members')->default(10)->comment('Maximum number of active members allowed in the package');
            $table->integer('storage_limit')->default(512)->comment('Storage limit in MB (default 512MB)');
            
            // Features toggled by boolean values
            $table->boolean('custom_branding')->default(false)->comment('Allow custom branding (logo, colors)');
            $table->boolean('api_access')->default(false)->comment('API access enabled for advanced users');
            $table->boolean('priority_support')->default(false)->comment('Priority support for premium plans');
            $table->integer('meeting_limit')->default(10)->comment('Max number of meetings allowed per month');
            $table->integer('event_limit')->default(10)->comment('Max number of events allowed per month');
            $table->integer('project_limit')->default(5)->comment('Max number of projects allowed in the system');
            $table->integer('asset_limit')->default(5)->comment('Max number of asset listings allowed in the system');
            $table->integer('document_limit')->default(10)->comment('Max number of documents allowed');
            $table->boolean('report')->default(true)->comment('Basic reporting enabled for all packages');
            $table->boolean('advanced_report')->default(false)->comment('Advanced reporting tools');
            $table->boolean('custom_report')->default(false)->comment('Custom reporting options');
            
            // Support levels
            $table->boolean('support')->default(true)->comment('Basic email support');
            $table->boolean('premium_support')->default(false)->comment('Premium support (live chat, etc.)');
            $table->boolean('dedicated_account_manager')->default(false)->comment('Dedicated account manager for large clients');
            
            // Customization features
            $table->boolean('custom_domain')->default(false)->comment('Support for custom domains');
            $table->boolean('custom_email_template')->default(false)->comment('Custom email templates');
            
            // Multi-currency payment support
            $table->boolean('multi_currency_payment')->default(false)->comment('Enable multi-currency payments for different regions');
            
            // Username creation feature
            $table->boolean('custom_username')->default(false)->comment('Enable creating a custom username');
            
            // Add web link option for package listing
            $table->boolean('web_profile')->default(false)->comment('Web profile to the package page for additional info');
            
            $table->timestamps();
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
