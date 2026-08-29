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
        Schema::create('about_timeline', function (Blueprint $table) {
            $table->id();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('about_timeline_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_timeline_id')->constrained('about_timeline')->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('year', 100);
            $table->text('description');
            $table->unique(['about_timeline_id', 'locale']);
        });

        $this->addLocaleCheck('about_timeline_translations');
    }

    public function down(): void
    {
        Schema::dropIfExists('about_timeline_translations');
        Schema::dropIfExists('about_timeline');
    }
};
