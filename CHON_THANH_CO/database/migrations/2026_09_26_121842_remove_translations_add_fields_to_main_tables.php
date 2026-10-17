<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name', 150)->after('slug');
            $table->text('description')->nullable()->after('name');
            $table->index('slug');
            $table->index('is_active');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('name', 200)->after('code');
            $table->text('description')->after('name');
            $table->string('strength_label', 50)->nullable()->after('description');
            $table->string('meta_title', 200)->nullable()->after('strength_label');
            $table->string('meta_description', 300)->nullable()->after('meta_title');
            $table->index('slug');
            $table->index('code');
            $table->index('category_id');
            $table->index('is_active');
            $table->index('is_featured');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('name', 200)->after('slug');
            $table->string('location', 200)->after('name');
            $table->text('description')->after('location');
            $table->string('meta_title', 200)->nullable()->after('description');
            $table->string('meta_description', 300)->nullable()->after('meta_title');
            $table->index('slug');
            $table->index('is_active');
            $table->index('is_featured');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->string('title', 250)->after('slug');
            $table->text('excerpt')->after('title');
            $table->longText('content')->nullable()->after('excerpt');
            $table->index('slug');
            $table->index('news_category_id');
            $table->index('published_at');
            $table->index('is_active');
        });

        Schema::table('news_categories', function (Blueprint $table) {
            $table->string('name', 150)->after('slug');
            $table->index('slug');
            $table->index('is_active');
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->string('question', 500)->after('id');
            $table->text('answer')->after('question');
            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->string('name', 150)->after('slug');
            $table->text('description')->nullable()->after('name');
            $table->index('slug');
            $table->index('is_active');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->string('title', 300)->after('section');
            $table->string('subtitle', 500)->nullable()->after('title');
            $table->text('text')->nullable()->after('subtitle');
            $table->string('button_text', 100)->nullable()->after('text');
            $table->index('is_active');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->string('name', 200)->after('slug');
            $table->text('description')->nullable()->after('name');
            $table->index('slug');
            $table->index('is_active');
        });

        Schema::table('home_stats', function (Blueprint $table) {
            $table->string('label', 150)->after('value');
            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::table('why_choose_us', function (Blueprint $table) {
            $table->string('title', 200)->after('icon');
            $table->text('description')->after('title');
            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::table('about_timeline', function (Blueprint $table) {
            $table->string('year', 100)->after('id');
            $table->text('description')->after('year');
            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::table('product_specs', function (Blueprint $table) {
            $table->string('label', 150)->after('value');
            $table->index('product_id');
            $table->index('sort_order');
        });

        Schema::table('project_materials', function (Blueprint $table) {
            $table->string('name', 150)->after('image');
            $table->text('detail')->after('name');
            $table->index('project_id');
            $table->index('product_id');
            $table->index('sort_order');
        });

        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('project_translations');
        Schema::dropIfExists('news_translations');
        Schema::dropIfExists('news_category_translations');
        Schema::dropIfExists('faq_translations');
        Schema::dropIfExists('application_translations');
        Schema::dropIfExists('banner_translations');
        Schema::dropIfExists('certificate_translations');
        Schema::dropIfExists('home_stat_translations');
        Schema::dropIfExists('why_choose_us_translations');
        Schema::dropIfExists('about_timeline_translations');
        Schema::dropIfExists('product_spec_translations');
        Schema::dropIfExists('project_material_translations');
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'strength_label', 'meta_title', 'meta_description']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['name', 'location', 'description', 'meta_title', 'meta_description']);
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['title', 'excerpt', 'content']);
        });

        Schema::table('news_categories', function (Blueprint $table) {
            $table->dropColumn(['name']);
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn(['question', 'answer']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['title', 'subtitle', 'text', 'button_text']);
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });

        Schema::table('home_stats', function (Blueprint $table) {
            $table->dropColumn(['label']);
        });

        Schema::table('why_choose_us', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });

        Schema::table('about_timeline', function (Blueprint $table) {
            $table->dropColumn(['year', 'description']);
        });

        Schema::table('product_specs', function (Blueprint $table) {
            $table->dropColumn(['label']);
        });

        Schema::table('project_materials', function (Blueprint $table) {
            $table->dropColumn(['name', 'detail']);
        });
    }
};
