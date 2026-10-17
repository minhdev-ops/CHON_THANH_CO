<?php

/*
|--------------------------------------------------------------------------
| CHON THANH Geosynthetics - Consolidated Database Schema
|--------------------------------------------------------------------------
|
| This file is generated from all migrations and represents the complete
| database structure. Similar to Rails' schema.rb but for Laravel.
|
| Generated: {{ date('Y-m-d H:i:s') }}
| Laravel Version: 12.x
|
*/

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return [
    /*
    |--------------------------------------------------------------------------
    | Core Tables
    |--------------------------------------------------------------------------
    */
    'users' => function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    },

    'password_reset_tokens' => function (Blueprint $table) {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    },

    'sessions' => function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    },

    /*
    |--------------------------------------------------------------------------
    | Categories & Translations
    |--------------------------------------------------------------------------
    */
    'categories' => function (Blueprint $table) {
        $table->id();
        $table->string('slug', 150);
        $table->unsignedInteger('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
        $table->unique(['slug', 'deleted_at']);
    },

    'category_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('name', 150);
        $table->text('description')->nullable();
        $table->unique(['category_id', 'locale']);
    },

    /*
    |--------------------------------------------------------------------------
    | Applications & Translations
    |--------------------------------------------------------------------------
    */
    'applications' => function (Blueprint $table) {
        $table->id();
        $table->string('slug', 150);
        $table->unsignedInteger('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
        $table->unique(['slug', 'deleted_at']);
    },

    'application_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('application_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('name', 150);
        $table->text('description')->nullable();
        $table->unique(['application_id', 'locale']);
    },

    /*
    |--------------------------------------------------------------------------
    | Products & Related
    |--------------------------------------------------------------------------
    */
    'products' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained()->restrictOnDelete();
        $table->string('slug', 150);
        $table->string('code', 100)->unique();
        $table->string('image', 255);
        $table->decimal('strength_min', 8, 2)->nullable();
        $table->decimal('strength_max', 8, 2)->nullable();
        $table->boolean('is_featured')->default(false);
        $table->boolean('is_active')->default(true);
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();
        $table->softDeletes();
        $table->unique(['slug', 'deleted_at']);
        $table->index(['category_id', 'is_active']);
        $table->index(['is_active', 'sort_order']);
        $table->index(['is_featured', 'is_active']);
    },

    'product_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('name', 200);
        $table->text('description');
        $table->string('strength_label', 50)->nullable();
        $table->string('meta_title', 200)->nullable();
        $table->string('meta_description', 300)->nullable();
        $table->unique(['product_id', 'locale']);
    },

    'product_specs' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->string('icon', 50)->nullable();
        $table->string('value', 100);
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();
    },

    'product_spec_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_spec_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('label', 150);
        $table->unique(['product_spec_id', 'locale']);
    },

    'product_images' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->string('image', 255);
        $table->string('alt', 255)->nullable();
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();
    },

    'product_documents' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->string('title', 200);
        $table->string('file_url', 255);
        $table->unsignedInteger('file_size')->nullable();
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();
        $table->index(['product_id', 'sort_order']);
    },

    'product_application' => function (Blueprint $table) {
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->foreignId('application_id')->constrained()->cascadeOnDelete();
        $table->timestamps();
        $table->primary(['product_id', 'application_id']);
    },

    /*
    |--------------------------------------------------------------------------
    | Projects & Related
    |--------------------------------------------------------------------------
    */
    'projects' => function (Blueprint $table) {
        $table->id();
        $table->string('slug', 150);
        $table->string('period', 50);
        $table->string('area', 50)->nullable();
        $table->string('hero_image', 255);
        $table->string('desc_image', 255)->nullable();
        $table->boolean('is_active')->default(true);
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();
        $table->softDeletes();
        $table->unique(['slug', 'deleted_at']);
    },

    'project_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('name', 200);
        $table->string('location', 200);
        $table->text('description');
        $table->string('meta_title', 200)->nullable();
        $table->string('meta_description', 300)->nullable();
        $table->unique(['project_id', 'locale']);
    },

    'project_images' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained()->cascadeOnDelete();
        $table->string('image', 255);
        $table->string('alt', 255)->nullable();
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();
    },

    'project_materials' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
        $table->string('image', 255);
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();
    },

    'project_material_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_material_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('name', 150);
        $table->text('detail');
        $table->unique(['project_material_id', 'locale']);
    },

    /*
    |--------------------------------------------------------------------------
    | Certificates & Translations
    |--------------------------------------------------------------------------
    */
    'certificates' => function (Blueprint $table) {
        $table->id();
        $table->string('slug', 150);
        $table->string('image', 255);
        $table->string('file', 255)->nullable();
        $table->boolean('is_active')->default(true);
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();
        $table->softDeletes();
        $table->unique(['slug', 'deleted_at']);
    },

    'certificate_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('certificate_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('name', 200);
        $table->text('description')->nullable();
        $table->unique(['certificate_id', 'locale']);
    },

    /*
    |--------------------------------------------------------------------------
    | News & Categories
    |--------------------------------------------------------------------------
    */
    'news_categories' => function (Blueprint $table) {
        $table->id();
        $table->string('slug', 150);
        $table->unsignedInteger('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
        $table->unique(['slug', 'deleted_at']);
    },

    'news_category_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('news_category_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('name', 150);
        $table->unique(['news_category_id', 'locale']);
    },

    'news' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('news_category_id')->nullable()->constrained()->nullOnDelete();
        $table->string('slug', 150);
        $table->string('image', 255);
        $table->timestamp('published_at')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
        $table->unique(['slug', 'deleted_at']);
        $table->index(['news_category_id', 'published_at']);
    },

    'news_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('news_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('title', 250);
        $table->text('excerpt');
        $table->longText('content')->nullable();
        $table->unique(['news_id', 'locale']);
    },

    /*
    |--------------------------------------------------------------------------
    | FAQs
    |--------------------------------------------------------------------------
    */
    'faqs' => function (Blueprint $table) {
        $table->id();
        $table->unsignedInteger('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    },

    'faq_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('faq_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('question', 500);
        $table->text('answer');
        $table->unique(['faq_id', 'locale']);
    },

    /*
    |--------------------------------------------------------------------------
    | Contact Messages
    |--------------------------------------------------------------------------
    */
    'contact_messages' => function (Blueprint $table) {
        $table->id();
        $table->string('name', 150);
        $table->string('phone', 30);
        $table->string('email', 255);
        $table->string('company', 150)->nullable();
        $table->string('product', 150)->nullable();
        $table->json('products')->nullable();
        $table->text('message');
        $table->string('status', 20)->default('new');
        $table->timestamp('handled_at')->nullable();
        $table->timestamps();
        $table->index('status');
    },

    /*
    |--------------------------------------------------------------------------
    | Home & About Page Content
    |--------------------------------------------------------------------------
    */
    'home_stats' => function (Blueprint $table) {
        $table->id();
        $table->string('icon', 50);
        $table->string('value', 50);
        $table->unsignedInteger('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    },

    'home_stat_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('home_stat_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('label', 150);
        $table->unique(['home_stat_id', 'locale']);
    },

    'why_choose_us' => function (Blueprint $table) {
        $table->id();
        $table->string('icon', 50);
        $table->unsignedInteger('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    },

    'why_choose_us_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('why_choose_us_id')->constrained('why_choose_us')->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('title', 200);
        $table->text('description');
        $table->unique(['why_choose_us_id', 'locale']);
    },

    'about_timeline' => function (Blueprint $table) {
        $table->id();
        $table->integer('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    },

    'about_timeline_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('about_timeline_id')->constrained('about_timeline')->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('year', 100);
        $table->text('description');
        $table->unique(['about_timeline_id', 'locale']);
    },

    /*
    |--------------------------------------------------------------------------
    | Settings & Banners
    |--------------------------------------------------------------------------
    */
    'settings' => function (Blueprint $table) {
        $table->id();
        $table->string('key', 100)->unique();
        $table->text('value')->nullable();
        $table->string('group', 50)->nullable();
        $table->timestamps();
    },

    'banners' => function (Blueprint $table) {
        $table->id();
        $table->string('section', 50)->default('hero');
        $table->string('image', 255)->nullable();
        $table->string('link_to', 255)->nullable();
        $table->boolean('is_active')->default(true);
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamps();
        $table->softDeletes();
        $table->index('section');
    },

    'banner_translations' => function (Blueprint $table) {
        $table->id();
        $table->foreignId('banner_id')->constrained()->cascadeOnDelete();
        $table->char('locale', 5);
        $table->string('title', 300);
        $table->string('subtitle', 500)->nullable();
        $table->text('text')->nullable();
        $table->string('button_text', 100)->nullable();
        $table->unique(['banner_id', 'locale']);
    },

    /*
    |--------------------------------------------------------------------------
    | Pages (CMS)
    |--------------------------------------------------------------------------
    */
    'pages' => function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique();
        $table->json('title')->nullable();
        $table->json('content')->nullable();
        $table->json('meta_title')->nullable();
        $table->json('meta_description')->nullable();
        $table->boolean('is_active')->default(true);
        $table->integer('order')->default(0);
        $table->timestamps();
    },

    /*
    |--------------------------------------------------------------------------
    | Permissions (spatie/laravel-permission)
    |--------------------------------------------------------------------------
    */
    'permissions' => function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('name');
        $table->string('guard_name');
        $table->timestamps();
        $table->unique(['name', 'guard_name']);
    },

    'roles' => function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('name');
        $table->string('guard_name');
        $table->timestamps();
        $table->unique(['name', 'guard_name']);
    },

    'model_has_permissions' => function (Blueprint $table) {
        $table->unsignedBigInteger('permission_id');
        $table->string('model_type');
        $table->unsignedBigInteger('model_id');
        $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
        $table->foreign('permission_id')
            ->references('id')
            ->on('permissions')
            ->onDelete('cascade');
        $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
    },

    'model_has_roles' => function (Blueprint $table) {
        $table->unsignedBigInteger('role_id');
        $table->string('model_type');
        $table->unsignedBigInteger('model_id');
        $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
        $table->foreign('role_id')
            ->references('id')
            ->on('roles')
            ->onDelete('cascade');
        $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
    },

    'role_has_permissions' => function (Blueprint $table) {
        $table->unsignedBigInteger('permission_id');
        $table->unsignedBigInteger('role_id');
        $table->foreign('permission_id')
            ->references('id')
            ->on('permissions')
            ->onDelete('cascade');
        $table->foreign('role_id')
            ->references('id')
            ->on('roles')
            ->onDelete('cascade');
        $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
    },

    /*
    |--------------------------------------------------------------------------
    | Jobs & Cache (Laravel default)
    |--------------------------------------------------------------------------
    */
    'jobs' => function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('queue');
        $table->longText('payload');
        $table->unsignedInteger('attempts');
        $table->unsignedInteger('reserved_at')->nullable();
        $table->unsignedInteger('available_at')->nullable();
        $table->unsignedInteger('created_at');
    },

    'cache' => function (Blueprint $table) {
        $table->string('key')->primary();
        $table->longText('value');
        $table->integer('expiration');
    },
];