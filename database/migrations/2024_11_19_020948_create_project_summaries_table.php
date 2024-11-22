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
        Schema::create('project_summaries', function (Blueprint $table) {
            $table->id();

            // Foreign keys using foreignId and constrained
            $table->foreignId('org_project_id')
                ->constrained('org_projects')
                ->cascadeOnDelete()
                ->comment('Foreign key referencing the org_projects table');

            // Summary details
            $table->integer('total_member_participation')
                ->default(0)
                ->comment('Total number of members who participated in the project');
            $table->integer('total_guest_participation')
                ->default(0)
                ->comment('Total number of guests who participated in the project');
            $table->integer('total_participation')
                ->default(0)
                ->comment('Total number of participants in the project');
            $table->integer('total_beneficial_person')
                ->default(0)
                ->comment('Total number of individuals who benefited from the project');
            $table->integer('total_communities_impacted')
                ->default(0)
                ->comment('Total number of communities impacted by the project');
            $table->text('summary')
                ->nullable()
                ->comment('A brief summary of the project outcomes');
            $table->text('highlights')
                ->nullable()
                ->comment('Key highlights or achievements of the project');
            $table->text('feedback')
                ->nullable()
                ->comment('Feedback from participants or stakeholders');
            $table->text('challenges')
                ->nullable()
                ->comment('Challenges faced during the project');
            $table->text('suggestions')
                ->nullable()
                ->comment('Suggestions for future improvements');
            $table->text('financial_overview')
                ->nullable()
                ->comment('Overview of financial aspects of the project');

            $table->integer('total_expense')
                ->default(0)
                ->comment('Total expense for the project');

            // Attachments
            $table->string('image_attachment')->nullable()->comment('Path to any image attachment');
            $table->string('file_attachment')->nullable()->comment('Path to any file attachment');

            // Outcomes and Next Steps
            $table->text('outcomes')
                ->nullable()
                ->comment('Overall outcomes and results achieved by the project');
            $table->text('next_steps')
                ->nullable()
                ->comment('Planned next steps after the project');

            // Prepared and updated by users
            $table->string('created_by', 30)->nullable()->comment('User who created the project summary');
            $table->string('updated_by', 30)->nullable()->comment('User who last updated the project summary');

            $table->foreignId('privacy_setup_id')
                ->constrained('privacy_setups')
                ->onDelete('cascade')
                ->comment('Privacy level of the project summary (e.g., public, private, members only)');

            // Status flags
            $table->boolean('is_active')
                ->default(true)
                ->comment('Indicates whether the project summary is currently active')
                ->nullable();
            $table->boolean('is_publish')
                ->default(false)
                ->comment('Indicates whether the project summary is published');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_summaries');
    }
};
