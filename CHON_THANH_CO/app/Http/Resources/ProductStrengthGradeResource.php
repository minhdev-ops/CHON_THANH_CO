<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Một cấp cường độ trong bảng so sánh của dòng sản phẩm. */
class ProductStrengthGradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'grade' => $this->grade,
            'unit' => $this->unit,
            'note' => $this->note,
        ];
    }
}