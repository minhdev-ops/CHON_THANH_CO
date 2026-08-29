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
        Schema::create('home_stats', function (Blueprint $table) {
            $table->id();
            $table->string('icon', 50);
            $table->string('value', 50);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('home_stat_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_stat_id')->constrained()->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('label', 150);
            $table->unique(['home_stat_id', 'locale']);
        });

        $this->addLocaleCheck('home_stat_translations');
    }

    public function down(): void
    {
        Schema::dropIfExists('home_stat_translations');
        Schema::dropIfExists('home_stats');
    }
};
