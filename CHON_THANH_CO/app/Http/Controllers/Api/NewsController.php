<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Services\ContentService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(protected ContentService $service) {}

    public function index(Request $request)
    {
        $result = $this->service->news(
            $request->only(['category', 'cursor', 'limit']),
            app()->getLocale(),
            min(50, max(1, (int) ($request->input('limit') ?: 12))),
        );

        return response()->json([
            'data' => NewsResource::collection($result['items'])->resolve(),
            'next_cursor' => $result['next_cursor'],
        ]);
    }

    public function categories(Request $request)
    {
        return $this->service->newsCategories(app()->getLocale())
            ->map(fn ($category) => [
                'slug' => $category->slug,
                'name' => $category->translation()?->name,
            ])
            ->values();
    }

    public function show(string $slug): NewsResource
    {
        $news = $this->service->newsDetail($slug, app()->getLocale());

        abort_unless($news, 404);

        return NewsResource::make($news);
    }
}
