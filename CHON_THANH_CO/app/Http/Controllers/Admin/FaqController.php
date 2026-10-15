<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use Concerns\RendersAdminTables;

    public function index(Request $request)
    {
        $baseQuery = fn () => Faq::query();

        $applyFilters = function ($query, Request $request) {
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where('question', 'like', "%{$search}%");
            }
        };

        if ($this->wantsTableAjax($request)) {
            return $this->tableJson(
                $request,
                $baseQuery,
                $applyFilters,
                'admin.faqs.rows',
                itemsVar: 'faqs',
                sortable: [
                    'id' => 'id',
                    'question' => 'question',
                    'sort_order' => 'sort_order',
                    'is_active' => 'is_active',
                ],
                defaultOrder: ['sort_order' => 'asc'],
            );
        }

        $query = $baseQuery();
        $applyFilters($query, $request);

        $faqs = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faqs.form', ['faq' => null]);
    }

    public function store(Request $request)
    {
        $this->mergeViTranslations($request);
        $data = $this->validated($request);
        $faq = Faq::create([
            'question' => $data['question'],
            'answer' => $data['answer'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'Đã tạo FAQ.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.form', ['faq' => $faq]);
    }

    public function update(Request $request, Faq $faq)
    {
        $this->mergeViTranslations($request);
        $data = $this->validated($request);
        $faq->update([
            'question' => $data['question'],
            'answer' => $data['answer'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'Đã cập nhật FAQ.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'Đã xóa FAQ.');
    }

    private function mergeViTranslations(Request $request): void
    {
        $vi = $request->input('translations.vi', []);
        if (! is_array($vi)) {
            return;
        }

        foreach (['question', 'answer'] as $key) {
            if (! $request->filled($key) && filled($vi[$key] ?? null)) {
                $request->merge([$key => $vi[$key]]);
            }
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);
    }
}
