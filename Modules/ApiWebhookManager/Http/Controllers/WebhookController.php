<?php

// namespace App\Http\Controllers;

namespace Modules\ApiWebhookManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ApiWebhookManager\Services\WebhookService;

class WebhookController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Webhook-Signature');
        $modelType = $payload['model'] ?? '';

        // Get model table name from class
        $modelInstance = new $modelType;
        $tableName = $modelInstance->getTable();

        // Get secret for this model
        $secret = config("webhooks.models.{$tableName}.secret", config('ApiWebhookManager.signing_secret'));

        // Validate signature
        if (! $this->webhookService->validateSignature($signature, $payload, $secret)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            // Process the webhook
            switch ($payload['event']) {
                case 'created':
                    $this->handleCreated($payload);
                    break;
                case 'updated':
                    $this->handleUpdated($payload);
                    break;
                case 'deleted':
                    $this->handleDeleted($payload);
                    break;
                default:
                    return response()->json(['error' => 'Unknown event type'], 400);
            }

            return response()->json(['message' => 'Webhook processed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function handleCreated(array $payload): void
    {
        // Implement your creation logic here
    }

    protected function handleUpdated(array $payload): void
    {
        // Implement your update logic here
    }

    protected function handleDeleted(array $payload): void
    {
        // Implement your deletion logic here
    }
}
