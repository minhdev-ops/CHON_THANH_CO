<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AboutTimelineResource;
use App\Http\Resources\BannerResource;
use App\Http\Resources\HomeStatResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\WhyChooseUsResource;
use App\Services\HomeService;
use App\Services\ProductService;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        protected HomeService $home,
        protected ProductService $products,
        protected ProjectService $projects,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $locale = app()->getLocale();

        return response()->json([
            'banners' => BannerResource::collection($this->home->banners($locale)),
            'stats' => HomeStatResource::collection($this->home->stats($locale)),
            'why_choose_us' => WhyChooseUsResource::collection($this->home->whyChooseUs($locale)),
            'featured_products' => ProductResource::collection($this->products->featured($locale)),
            'latest_projects' => ProjectResource::collection($this->projects->featured($locale)),
        ]);
    }

    public function settings(Request $request): JsonResponse
    {
        $settings = $this->home->settings()->mapWithKeys(fn ($setting) => [$setting->key => $setting->value]);

        return response()->json($settings);
    }

    public function timeline(Request $request): JsonResponse
    {
        $locale = app()->getLocale();

        return response()->json([
            'data' => AboutTimelineResource::collection($this->home->timeline($locale))->resolve(),
        ]);
    }
}
