<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sentinel_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();
            $table->json('data');
            $table->enum('severity', ['info', 'warning', 'critical'])->index();
            $table->timestamp('created_at')->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sentinel_logs');
    }
};
