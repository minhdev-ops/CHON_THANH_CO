<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Models\Page;
use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    use SyncsTranslations;

    private const SLUG = 'privacy-policy';

    public function edit(Request $request)
    {
        $page = Page::where('slug', self::SLUG)->first();

        if (!$page) {
            $page = Page::create([
                'slug' => self::SLUG,
                'title' => ['vi' => '', 'en' => ''],
                'content' => ['vi' => '', 'en' => ''],
                'is_active' => true,
            ]);
        }

        return view('admin.privacy-policy.edit', compact('page'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'title.vi' => ['required', 'string', 'max:255'],
            'title.en' => ['required', 'string', 'max:255'],
            'content.vi' => ['required', 'string'],
            'content.en' => ['required', 'string'],
            'pdf_file' => ['nullable', 'string'], // path to uploaded PDF
        ]);

        $page = Page::where('slug', self::SLONG)->firstOrFail();

        // Prepare translations
        $translations = [
            'vi' => [
                'title' => $request->input('title.vi'),
                'content' => $request->input('content.vi'),
            ],
            'en' => [
                'title' => $request->input('title.en'),
                'content' => $request->input('content.en'),
            ],
        ];

        // Sync translatable fields (title, content)
        $this->syncTranslations($page, $translations, ['title', 'content']);

        // Handle PDF file: store in content under a key 'pdf_file' for each locale (same value)
        $pdfFile = $request->input('pdf_file');
        if ($pdfFile !== null) {
            // Ensure content arrays have pdf_file key
            $page->setTranslation('content', 'vi', array_merge(
                $page->getTranslation('content', 'vi') ?: [],
                ['pdf_file' => $pdfFile]
            ));
            $page->setTranslation('content', 'en', array_merge(
                $page->getTranslation('content', 'en') ?: [],
                ['pdf_file' => $pdfFile]
            ));
            $page->save();
        }

        return redirect()->route('admin.privacy-policy.edit')
            ->with('success', 'Đã lưu chính sách bảo mật.');
    }
}