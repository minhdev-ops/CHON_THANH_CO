<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Các cấp cường độ của một dòng sản phẩm (ART 7/9/12/14/15/25 kN/m...).
        // Trước đây các cấp này chỉ nằm chung trong description dạng văn bản,
        // không tái sử dụng được cho bảng so sánh hay bộ lọc.
        Schema::create('product_strength_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('grade', 100);
            $table->string('unit', 50)->nullable();
            $table->string('note', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_strength_grades');
    }
};