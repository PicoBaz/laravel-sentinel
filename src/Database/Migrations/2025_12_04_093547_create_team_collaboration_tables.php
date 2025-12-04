<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sentinel_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('sentinel_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('sentinel_teams')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['member', 'lead', 'admin'])->default('member');
            $table->integer('points')->default(0);
            $table->json('badges')->nullable();
            $table->boolean('active')->default(true);
            $table->json('notification_preferences')->nullable();
            $table->json('notification_channels')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('sentinel_team_responsibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('sentinel_teams')->onDelete('cascade');
            $table->string('log_type');
            $table->timestamps();

            $table->index(['team_id', 'log_type']);
        });

        Schema::create('sentinel_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('log_id')->constrained('sentinel_logs')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('sentinel_teams')->onDelete('cascade');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_comment')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('priority');
        });

        Schema::create('sentinel_team_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreignId('log_id')->nullable()->constrained('sentinel_logs')->onDelete('cascade');
            $table->foreignId('issue_id')->nullable()->constrained('sentinel_issues')->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index('type');
        });

        Schema::create('sentinel_issue_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('sentinel_issues')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->text('comment');
            $table->timestamps();

            $table->index('issue_id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sentinel_issue_comments');
        Schema::dropIfExists('sentinel_team_notifications');
        Schema::dropIfExists('sentinel_issues');
        Schema::dropIfExists('sentinel_team_responsibilities');
        Schema::dropIfExists('sentinel_team_members');
        Schema::dropIfExists('sentinel_teams');
    }
};
