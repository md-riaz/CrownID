<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'parentId' => $this->parent_id ? (string) $this->parent_id : null,
            'subGroups' => [],
        ];
    }
}
