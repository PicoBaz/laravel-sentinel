<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sentinel_security_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->unique()->index();
            $table->string('reason')->nullable();
            $table->integer('threat_count')->default(0);
            $table->integer('security_score')->default(100);
            $table->enum('threat_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->boolean('auto_blocked')->default(false);
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('last_threat_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sentinel_security_blacklist');
    }
};
