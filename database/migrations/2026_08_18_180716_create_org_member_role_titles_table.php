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
        Schema::create('org_member_role_titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_type_user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Foreign key: The user representing the organization type');

            $table->foreignId('individual_type_user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Foreign key: The user representing the individual type');

            $table->foreignId('org_role_title_id')
                ->constrained('org_role_titles')
                ->cascadeOnDelete()
                ->comment('Foreign key: The role title assigned to the individual in the organization');
            
            $table->unique([
                    'org_type_user_id',
                    'individual_type_user_id',
                    'org_role_title_id'
                ], 'unique_org_individual_role'); // custom index name

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_member_role_titles');
    }
};