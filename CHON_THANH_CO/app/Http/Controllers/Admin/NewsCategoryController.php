<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsCategoryController extends Controller
{
    use SyncsTranslations;

    public function index()
    {
        $categories = NewsCategory::with('translations')->withCount('news')->orderBy('sort_order')->get();

        return view('admin.news-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.news-categories.form', ['category' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $category = NewsCategory::create([
            'slug' => $this->makeSlug($data),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($category, $data['translations'], ['name']);

        return redirect()->route('admin.news-categories.index')->with('success', 'Đã tạo danh mục tin tức.');
    }

    public function edit(NewsCategory $news_category)
    {
        return view('admin.news-categories.form', ['category' => $news_category->load('translations')]);
    }

    public function update(Request $request, NewsCategory $news_category)
    {
        $data = $this->validated($request);
        $news_category->update([
            'slug' => $this->makeSlug($data, $news_category),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($news_category, $data['translations'], ['name']);

        return redirect()->route('admin.news-categories.index')->with('success', 'Đã cập nhật danh mục tin tức.');
    }

    public function destroy(NewsCategory $news_category)
    {
        if ($news_category->news()->exists()) {
            return back()->with('error', 'Không thể xóa danh mục đang chứa bài viết.');
        }

        $news_category->delete();

        return redirect()->route('admin.news-categories.index')->with('success', 'Đã xóa danh mục tin tức.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'slug' => ['nullable', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'translations.vi.name' => ['required', 'string', 'max:150'],
            'translations.en.name' => ['nullable', 'string', 'max:150'],
        ]);
    }

    private function makeSlug(array $data, ?NewsCategory $existing = null): string
    {
        if (! empty($data['slug'])) {
            return Str::slug($data['slug']);
        }

        $name = $data['translations']['vi']['name'] ?? '';

        return Str::slug($name) ?: ($existing?->slug ?? 'news-category-' . Str::random(6));
    }
}
