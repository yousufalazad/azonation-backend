<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An organisation's public profile: who it is, what it does, mission and vision.
 * The OrgProfile model and the "Fundamental info" page used this table,
 * but no migration created it, so saving the profile always failed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('org_profiles')) {
            return;
        }
        Schema::create('org_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete()
                ->comment('The organisation account');
            $table->string('short_description', 255)->nullable();
            $table->text('detail_description')->nullable();
            $table->text('who_we_are')->nullable();
            $table->text('what_we_do')->nullable();
            $table->text('how_we_do')->nullable();
            $table->text('mission')->nullable();
            $table->text('vision')->nullable();
            $table->text('value')->nullable();
            $table->text('areas_of_focus')->nullable();
            $table->text('causes')->nullable();
            $table->text('impact')->nullable();
            $table->text('why_join_us')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->date('organising_date')->nullable();
            $table->date('foundation_date')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_profiles');
    }
};
