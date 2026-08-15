<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'roles' => $this->getRoleNames(), // from spatie/laravel-permission
            'email_verified' => ! is_null($this->email_verified_at),
            'company_id' => $this->company_id,
            'driver' => $this->whenLoaded('driver', fn () => ['id' => $this->driver->id, 'phone' => $this->driver->phone]),
            'company' => $this->whenLoaded('company', fn () => [
                'id' => $this->company->id,
                'name' => $this->company->name,
                'status' => $this->company->status,
            ]),
            'last_login_at' => $this->last_login_at,
        ];
    }
}