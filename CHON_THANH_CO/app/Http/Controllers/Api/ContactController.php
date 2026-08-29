<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __construct(protected ContactService $service) {}

    public function store(StoreContactRequest $request): JsonResponse
    {
        $message = $this->service->store($request->validated());

        return response()->json([
            'message' => 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.',
            'data' => ['id' => $message->id],
        ], 201);
    }
}
