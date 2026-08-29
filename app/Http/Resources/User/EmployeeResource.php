<?php

namespace App\Http\Resources\User;

use App\Http\Resources\User\Concerns\HasPresence;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    use HasPresence;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_name' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image,
            'role' => $this->role,
            'status' => $this->status,
            'subjects' => $this->subjects->map(fn($subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
            ]),
            'last_login_at' => optional($this->last_login_at)->toISOString(),
            'presence' => $this->presence(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

        ];
    }
}
