<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor', 150);
            $table->string('action', 50);
            $table->string('model_type', 150);
            $table->unsignedBigInteger('model_id');
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
