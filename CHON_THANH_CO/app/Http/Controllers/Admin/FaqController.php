<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use SyncsTranslations;

    public function index()
    {
        $faqs = Faq::with('translations')->orderBy('sort_order')->get();

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faqs.form', ['faq' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $faq = Faq::create([
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($faq, $data['translations'], ['question', 'answer']);

        return redirect()->route('admin.faqs.index')->with('success', 'Đã tạo FAQ.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.form', ['faq' => $faq->load('translations')]);
    }

    public function update(Request $request, Faq $faq)
    {
        $data = $this->validated($request);
        $faq->update([
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($faq, $data['translations'], ['question', 'answer']);

        return redirect()->route('admin.faqs.index')->with('success', 'Đã cập nhật FAQ.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'Đã xóa FAQ.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'translations.vi.question' => ['required', 'string', 'max:500'],
            'translations.vi.answer' => ['required', 'string'],
            'translations.en.question' => ['nullable', 'string', 'max:500'],
            'translations.en.answer' => ['nullable', 'string'],
        ]);
    }
}
