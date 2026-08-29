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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('section', 50)->default('hero');
            $table->string('image', 255)->nullable();
            $table->string('link_to', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('section');
        });

        Schema::create('banner_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')->constrained()->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('title', 300);
            $table->string('subtitle', 500)->nullable();
            $table->text('text')->nullable();
            $table->string('button_text', 100)->nullable();
            $table->unique(['banner_id', 'locale']);
        });

        $this->addLocaleCheck('banner_translations');
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_translations');
        Schema::dropIfExists('banners');
    }
};
