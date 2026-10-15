<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    use Concerns\RendersAdminTables;

    public function index(Request $request)
    {
        $baseQuery = fn () => News::with('category');

        $applyFilters = function ($query, Request $request) {
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('slug', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            }
        };

        if ($this->wantsTableAjax($request)) {
            return $this->tableJson(
                $request,
                $baseQuery,
                $applyFilters,
                'admin.news.rows',
                itemsVar: 'news',
                sortable: [
                    'id' => 'id',
                    'title' => 'title',
                    'published_at' => 'published_at',
                    'is_active' => 'is_active',
                ],
                defaultOrder: ['published_at' => 'desc'],
            );
        }

        $query = $baseQuery();
        $applyFilters($query, $request);

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
        $this->mergeViTranslations($request);
        $data = $this->validated($request);
        $item = News::create([
            'news_category_id' => $data['news_category_id'] ?? null,
            'slug' => $this->makeSlug($data),
            'title' => $data['title'],
            'excerpt' => $data['excerpt'],
            'content' => $data['content'] ?? null,
            'image' => $data['image'],
            'published_at' => $data['published_at'] ?? now(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.news.index')->with('success', 'Đã tạo bài viết.');
    }

    public function edit(News $item)
    {
        return view('admin.news.form', [
            'item' => $item->load('category'),
            'categories' => NewsCategory::all(),
        ]);
    }

    public function update(Request $request, News $item)
    {
        $this->mergeViTranslations($request);
        $data = $this->validated($request);
        $item->update([
            'news_category_id' => $data['news_category_id'] ?? null,
            'slug' => $this->makeSlug($data, $item),
            'title' => $data['title'],
            'excerpt' => $data['excerpt'],
            'content' => $data['content'] ?? null,
            'image' => $data['image'],
            'published_at' => $data['published_at'] ?? $item->published_at,
            'is_active' => $request->boolean('is_active'),
        ]);

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

    /**
     * Form dùng partial translation-section nên title/excerpt/content đi theo
     * translations[vi][...]. Copy chúng sang khóa thường để validate() hoạt
     * động. Nếu thiếu hàm này, mọi lần lưu bài từ form đều báo lỗi "title is
     * required" dù admin đã điền đủ.
     */
    private function mergeViTranslations(Request $request): void
    {
        $vi = $request->input('translations.vi', []);
        if (! is_array($vi)) {
            return;
        }

        foreach (['title', 'excerpt', 'content'] as $key) {
            if (! $request->filled($key) && filled($vi[$key] ?? null)) {
                $request->merge([$key => $vi[$key]]);
            }
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'news_category_id' => ['nullable', 'exists:news_categories,id'],
            'slug' => ['nullable', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:250'],
            'excerpt' => ['required', 'string'],
            'content' => ['nullable', 'string'],
            'image' => ['required', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);
    }

    private function makeSlug(array $data, ?News $existing = null): string
    {
        if (! empty($data['slug'])) {
            return Str::slug($data['slug']);
        }

        $title = $data['title'] ?? '';

        return Str::slug($title) ?: ($existing?->slug ?? 'news-'.Str::random(6));
    }
}
