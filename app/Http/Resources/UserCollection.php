<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total() ?? $this->count(),
                'per_page' => $this->perPage() ?? null,
                'current_page' => $this->currentPage() ?? null,
                'last_page' => $this->lastPage() ?? null,
            ],
        ];
    }
}
