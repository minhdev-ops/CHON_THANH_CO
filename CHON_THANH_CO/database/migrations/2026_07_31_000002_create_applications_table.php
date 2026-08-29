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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 150);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['slug', 'deleted_at']);
        });

        Schema::create('application_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->char('locale', 5);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unique(['application_id', 'locale']);
        });

        $this->addLocaleCheck('application_translations');
    }

    public function down(): void
    {
        Schema::dropIfExists('application_translations');
        Schema::dropIfExists('applications');
    }
};
