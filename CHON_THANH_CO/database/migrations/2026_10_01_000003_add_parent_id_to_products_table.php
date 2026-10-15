<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Gom các mã cùng dòng (ART 12→280, GET 5→500) dưới một sản phẩm cha
        // để trang chi tiết hiển thị bảng chọn loại.
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('products')->nullOnDelete();
            $table->index(['parent_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id', 'is_active']);
            $table->dropColumn('parent_id');
        });
    }
};