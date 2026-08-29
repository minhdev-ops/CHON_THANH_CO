<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Http\Controllers\Controller;
use App\Models\HomeStat;
use Illuminate\Http\Request;

class HomeStatController extends Controller
{
    use SyncsTranslations;

    public function index()
    {
        $stats = HomeStat::with('translations')->orderBy('sort_order')->get();

        return view('admin.home-stats.index', compact('stats'));
    }

    public function create()
    {
        return view('admin.home-stats.form', ['stat' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $stat = HomeStat::create([
            'icon' => $data['icon'],
            'value' => $data['value'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($stat, $data['translations'], ['label']);

        return redirect()->route('admin.home-stats.index')->with('success', 'Đã tạo số liệu.');
    }

    public function edit(HomeStat $stat)
    {
        return view('admin.home-stats.form', ['stat' => $stat->load('translations')]);
    }

    public function update(Request $request, HomeStat $stat)
    {
        $data = $this->validated($request);
        $stat->update([
            'icon' => $data['icon'],
            'value' => $data['value'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($stat, $data['translations'], ['label']);

        return redirect()->route('admin.home-stats.index')->with('success', 'Đã cập nhật số liệu.');
    }

    public function destroy(HomeStat $stat)
    {
        $stat->delete();

        return redirect()->route('admin.home-stats.index')->with('success', 'Đã xóa số liệu.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'icon' => ['required', 'string', 'max:50'],
            'value' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'translations.vi.label' => ['required', 'string', 'max:150'],
            'translations.en.label' => ['nullable', 'string', 'max:150'],
        ]);
    }
}
