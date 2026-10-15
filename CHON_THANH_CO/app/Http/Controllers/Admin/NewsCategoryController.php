<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsCategoryController extends Controller
{
    use Concerns\RendersAdminTables;

    public function index(Request $request)
    {
        $baseQuery = fn () => NewsCategory::withCount('news');

        $applyFilters = function ($query, Request $request) {
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }
        };

        if ($this->wantsTableAjax($request)) {
            return $this->tableJson(
                $request,
                $baseQuery,
                $applyFilters,
                'admin.news-categories.rows',
                itemsVar: 'categories',
                sortable: [
                    'id' => 'id',
                    'name' => 'name',
                    'sort_order' => 'sort_order',
                    'news_count' => 'news_count',
                    'is_active' => 'is_active',
                ],
                defaultOrder: ['sort_order' => 'asc'],
            );
        }

        $query = $baseQuery();
        $applyFilters($query, $request);

        $categories = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        return view('admin.news-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.news-categories.form', ['category' => null]);
    }

    public function store(Request $request)
    {
        $this->mergeViTranslations($request);
        $data = $this->validated($request);
        $category = NewsCategory::create([
            'slug' => $this->makeSlug($data),
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.news-categories.index')->with('success', 'Đã tạo danh mục tin tức.');
    }

    public function edit(NewsCategory $news_category)
    {
        return view('admin.news-categories.form', ['category' => $news_category]);
    }

    public function update(Request $request, NewsCategory $news_category)
    {
        $this->mergeViTranslations($request);
        $data = $this->validated($request);
        $news_category->update([
            'slug' => $this->makeSlug($data, $news_category),
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

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

    private function mergeViTranslations(Request $request): void
    {
        $vi = $request->input('translations.vi', []);
        if (! is_array($vi)) {
            return;
        }

        foreach (['name'] as $key) {
            if (! $request->filled($key) && filled($vi[$key] ?? null)) {
                $request->merge([$key => $vi[$key]]);
            }
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'slug' => ['nullable', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);
    }

    private function makeSlug(array $data, ?NewsCategory $existing = null): string
    {
        if (! empty($data['slug'])) {
            return Str::slug($data['slug']);
        }

        $name = $data['name'] ?? '';

        return Str::slug($name) ?: ($existing?->slug ?? 'news-category-'.Str::random(6));
    }
}
