<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use SyncsTranslations;

    public function index()
    {
        $categories = Category::with('translations')->withCount('products')->orderBy('sort_order')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $category = Category::create([
            'slug' => $this->makeSlug($data),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($category, $data['translations'], ['name', 'description']);

        return redirect()->route('admin.categories.index')->with('success', 'Đã tạo danh mục.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', ['category' => $category->load('translations')]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request);
        $category->update([
            'slug' => $this->makeSlug($data, $category),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($category, $data['translations'], ['name', 'description']);

        return redirect()->route('admin.categories.index')->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Không thể xóa danh mục đang chứa sản phẩm.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Đã xóa danh mục.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'slug' => ['nullable', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'translations.vi.name' => ['required', 'string', 'max:150'],
            'translations.vi.description' => ['nullable', 'string'],
            'translations.en.name' => ['nullable', 'string', 'max:150'],
            'translations.en.description' => ['nullable', 'string'],
        ]);

        return $data;
    }

    private function makeSlug(array $data, ?Category $existing = null): string
    {
        if (! empty($data['slug'])) {
            return Str::slug($data['slug']);
        }

        $name = $data['translations']['vi']['name'] ?? '';

        return Str::slug($name) ?: ($existing?->slug ?? 'category-' . Str::random(6));
    }
}
