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
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('name', 200);
            $table->text('description');
            $table->string('strength_label', 50)->nullable();
            $table->string('meta_title', 200)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->unique(['product_id', 'locale']);
        });

        $this->addLocaleCheck('product_translations');
    }

    public function down(): void
    {
        Schema::dropIfExists('product_translations');
    }
};
