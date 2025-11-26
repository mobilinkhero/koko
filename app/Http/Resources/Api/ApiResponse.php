<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponse extends JsonResource
{
    /**
     * Create a new resource instance.
     */
    public function __construct($resource, $message = null, $success = true)
    {
        parent::__construct($resource);
        $this->message = $message;
        $this->success = $success;
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'success' => $this->success,
            'message' => $this->when($this->message, $this->message),
            'data' => $this->resource,
        ];
    }

    /**
     * Create a success response
     */
    public static function success($data = null, $message = null): self
    {
        return new self($data, $message, true);
    }

    /**
     * Create an error response
     */
    public static function error($message, $data = null): self
    {
        return new self($data, $message, false);
    }
}
