<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Help requests to the Azonation team: from signed-in accounts (with a conversation)
// and from the public Contact us form (user_id is empty)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('org_id')->nullable()->index(); // the organisation the person was working in
            $table->string('name', 120);
            $table->string('email', 190);
            $table->string('category', 30)->default('other');
            $table->string('subject', 150);
            $table->enum('status', ['open', 'answered', 'closed'])->default('open')->index();
            $table->string('source', 20)->default('app'); // app | contact_form
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_request_id')->constrained('support_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_staff')->default(false);
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_requests');
    }
};
