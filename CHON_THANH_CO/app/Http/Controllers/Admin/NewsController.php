<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    use SyncsTranslations;

    public function index(Request $request)
    {
        $query = News::with('translations', 'category');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                    ->orWhereHas('translations', function ($t) use ($search) {
                        $t->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $news = $query->latest('published_at')->paginate(15)->withQueryString();

        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        return view('admin.news.form', [
            'item' => null,
            'categories' => NewsCategory::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $item = News::create([
            'news_category_id' => $data['news_category_id'] ?? null,
            'slug' => $this->makeSlug($data),
            'image' => $data['image'],
            'published_at' => $data['published_at'] ?? now(),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($item, $data['translations'], ['title', 'excerpt', 'content']);

        return redirect()->route('admin.news.index')->with('success', 'Đã tạo bài viết.');
    }

    public function edit(News $item)
    {
        return view('admin.news.form', [
            'item' => $item->load('translations', 'category'),
            'categories' => NewsCategory::all(),
        ]);
    }

    public function update(Request $request, News $item)
    {
        $data = $this->validated($request);
        $item->update([
            'news_category_id' => $data['news_category_id'] ?? null,
            'slug' => $this->makeSlug($data, $item),
            'image' => $data['image'],
            'published_at' => $data['published_at'] ?? $item->published_at,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($item, $data['translations'], ['title', 'excerpt', 'content']);

        return redirect()->route('admin.news.index')->with('success', 'Đã cập nhật bài viết.');
    }

    public function destroy(News $item)
    {
        $item->delete();

        return redirect()->route('admin.news.index')->with('success', 'Đã xóa bài viết.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:delete,activate,deactivate'],
        ]);

        $models = News::whereIn('id', $data['ids'])->get();
        $count = $models->count();

        match ($data['action']) {
            'delete' => $models->each->delete(),
            'activate' => $models->each(fn (News $model) => $model->update(['is_active' => true])),
            'deactivate' => $models->each(fn (News $model) => $model->update(['is_active' => false])),
        };

        $messages = [
            'delete' => "Đã xóa {$count} bài viết.",
            'activate' => "Đã hiển thị {$count} bài viết.",
            'deactivate' => "Đã ẩn {$count} bài viết.",
        ];

        return redirect()->route('admin.news.index')->with('success', $messages[$data['action']]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'news_category_id' => ['nullable', 'exists:news_categories,id'],
            'slug' => ['nullable', 'string', 'max:150'],
            'image' => ['required', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
            'translations.vi.title' => ['required', 'string', 'max:250'],
            'translations.vi.excerpt' => ['required', 'string'],
            'translations.vi.content' => ['nullable', 'string'],
            'translations.en.title' => ['nullable', 'string', 'max:250'],
            'translations.en.excerpt' => ['nullable', 'string'],
            'translations.en.content' => ['nullable', 'string'],
        ]);
    }

    private function makeSlug(array $data, ?News $existing = null): string
    {
        if (! empty($data['slug'])) {
            return Str::slug($data['slug']);
        }

        $title = $data['translations']['vi']['title'] ?? '';

        return Str::slug($title) ?: ($existing?->slug ?? 'news-'.Str::random(6));
    }
}
