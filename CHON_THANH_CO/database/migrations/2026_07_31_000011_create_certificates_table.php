<?php

use App\Support\Concerns\AddsLocaleCheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use AddsLocaleCheck;

    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 150);
            $table->string('image', 255);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['slug', 'deleted_at']);
        });

        Schema::create('certificate_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained()->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->unique(['certificate_id', 'locale']);
        });

        $this->addLocaleCheck('certificate_translations');
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_translations');
        Schema::dropIfExists('certificates');
    }
};
