<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $service) {}

    public function index(Request $request)
    {
        $result = $this->service->list(
            $request->only(['cursor', 'limit']),
            app()->getLocale(),
            min(50, max(1, (int) ($request->input('limit') ?: 12))),
        );

        return response()->json([
            'data' => ProjectResource::collection($result['items'])->resolve(),
            'next_cursor' => $result['next_cursor'],
        ]);
    }

    public function show(string $slug): ProjectResource
    {
        $project = $this->service->findBySlug($slug, app()->getLocale());

        abort_unless($project, 404);

        return ProjectResource::make($project);
    }
}
