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
        Schema::create('why_choose_us', function (Blueprint $table) {
            $table->id();
            $table->string('icon', 50);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('why_choose_us_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('why_choose_us_id')->constrained('why_choose_us')->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('title', 200);
            $table->text('description');
            $table->unique(['why_choose_us_id', 'locale']);
        });

        $this->addLocaleCheck('why_choose_us_translations');
    }

    public function down(): void
    {
        Schema::dropIfExists('why_choose_us_translations');
        Schema::dropIfExists('why_choose_us');
    }
};
