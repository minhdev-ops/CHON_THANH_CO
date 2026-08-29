<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Http\Controllers\Controller;
use App\Models\WhyChooseUs;
use Illuminate\Http\Request;

class WhyChooseUsController extends Controller
{
    use SyncsTranslations;

    public function index()
    {
        $items = WhyChooseUs::with('translations')->orderBy('sort_order')->get();

        return view('admin.why-choose-us.index', compact('items'));
    }

    public function create()
    {
        return view('admin.why-choose-us.form', ['item' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $item = WhyChooseUs::create([
            'icon' => $data['icon'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($item, $data['translations'], ['title', 'description']);

        return redirect()->route('admin.why-choose-us.index')->with('success', 'Đã tạo mục.');
    }

    public function edit(WhyChooseUs $item)
    {
        return view('admin.why-choose-us.form', ['item' => $item->load('translations')]);
    }

    public function update(Request $request, WhyChooseUs $item)
    {
        $data = $this->validated($request);
        $item->update([
            'icon' => $data['icon'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($item, $data['translations'], ['title', 'description']);

        return redirect()->route('admin.why-choose-us.index')->with('success', 'Đã cập nhật mục.');
    }

    public function destroy(WhyChooseUs $item)
    {
        $item->delete();

        return redirect()->route('admin.why-choose-us.index')->with('success', 'Đã xóa mục.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'icon' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'translations.vi.title' => ['required', 'string', 'max:200'],
            'translations.vi.description' => ['required', 'string'],
            'translations.en.title' => ['nullable', 'string', 'max:200'],
            'translations.en.description' => ['nullable', 'string'],
        ]);
    }
}
