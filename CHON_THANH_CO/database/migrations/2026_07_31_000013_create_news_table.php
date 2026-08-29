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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug', 150);
            $table->string('image', 255);
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['slug', 'deleted_at']);
            $table->index(['news_category_id', 'published_at']);
        });

        Schema::create('news_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained()->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('title', 250);
            $table->text('excerpt');
            $table->unique(['news_id', 'locale']);
        });

        $this->addLocaleCheck('news_translations');
    }

    public function down(): void
    {
        Schema::dropIfExists('news_translations');
        Schema::dropIfExists('news');
    }
};
