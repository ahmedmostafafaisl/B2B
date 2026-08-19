<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleEmployeeResource extends JsonResource
{

    public function toArray(Request $request): array
    {


        return [
            'id' => $this->id,
            'user_name' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image,
            'type' => $this->type ?? null,
            'role' => $this->getRoleNames()->first(),
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'permissions' => $this->groupedPermissions(),
        ];
    }




    public function groupedPermissions()
    {
        return $this->permissions->groupBy(function ($permission) {
            return explode(' ', $permission->name, 2)[1] ?? 'other';
        })->map(function ($group) {
            return $group->pluck('name')->values();
        });
    }
}
