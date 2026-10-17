<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('warehouse', 20)->nullable()->after('category_id');
            $table->unsignedInteger('length_mm')->nullable()->after('strength_max');
            $table->unsignedInteger('width_mm')->nullable()->after('length_mm');
            $table->unsignedInteger('height_mm')->nullable()->after('width_mm');
            $table->string('mesh_size', 10)->nullable()->after('height_mm');
            $table->decimal('wire_diameter_mm', 3, 1)->nullable()->after('mesh_size');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['warehouse', 'is_active']);
            $table->index('mesh_size');
        });

        DB::table('products')->update(['warehouse' => 'merchandise']);

        $gabionCategoryId = DB::table('categories')
            ->where('slug', 'ro-da')
            ->value('id');

        if ($gabionCategoryId) {
            DB::table('products')
                ->where('category_id', $gabionCategoryId)
                ->update(['warehouse' => 'finished_goods']);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['warehouse', 'is_active']);
            $table->dropIndex(['mesh_size']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'warehouse',
                'length_mm',
                'width_mm',
                'height_mm',
                'mesh_size',
                'wire_diameter_mm',
            ]);
        });
    }
};
