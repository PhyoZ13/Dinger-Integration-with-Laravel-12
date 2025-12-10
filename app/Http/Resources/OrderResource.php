<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'order_id' => $this->order_id,
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'dinger_transaction_id' => $this->dinger_transaction_id,
            'dinger_provider_name' => $this->dinger_provider_name,
            'dinger_method_name' => $this->dinger_method_name,
            'payment_completed_at' => $this->payment_completed_at?->toIso8601String(),
            'payment_failed_at' => $this->payment_failed_at?->toIso8601String(),
            'payment_failure_reason' => $this->payment_failure_reason,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
        ];
    }
}



