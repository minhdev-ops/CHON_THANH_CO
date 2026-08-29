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
        Schema::create('project_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image', 255);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_material_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_material_id')->constrained()->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('name', 150);
            $table->text('detail');
            $table->unique(['project_material_id', 'locale']);
        });

        $this->addLocaleCheck('project_material_translations');
    }

    public function down(): void
    {
        Schema::dropIfExists('project_material_translations');
        Schema::dropIfExists('project_materials');
    }
};
