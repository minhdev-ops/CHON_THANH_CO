<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cho phép đối tác chưa có logo: dải đối tác sẽ hiển thị tên dạng chữ.
        Schema::table('partners', function (Blueprint $table) {
            $table->string('image', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('image', 255)->nullable(false)->change();
        });
    }
};
