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
        Schema::create('product_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('icon', 50)->nullable();
            $table->string('value', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_spec_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_spec_id')->constrained()->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('label', 150);
            $table->unique(['product_spec_id', 'locale']);
        });

        $this->addLocaleCheck('product_spec_translations');
    }

    public function down(): void
    {
        Schema::dropIfExists('product_spec_translations');
        Schema::dropIfExists('product_specs');
    }
};
