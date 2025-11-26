<?php

namespace App\Http\Middleware;

use App\Models\PaymentWebhook;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $provider): Response
    {
        $payload = $request->getContent();

        // Get the webhook from database
        $webhook = PaymentWebhook::forProvider($provider)
            ->active()
            ->first();

        if (! $webhook) {
            return response()->json(['error' => 'No webhook configuration found'], 400);
        }

        $signature = '';
        $secret = $webhook->secret;

        // Extract the signature based on the provider
        switch ($provider) {
            case 'stripe':
                $signature = $request->header('Stripe-Signature');
                if (! $this->verifyStripeSignature($payload, $signature, $secret)) {
                    return $this->invalidSignatureResponse($provider);
                }
                break;

            case 'razorpay':
                $signature = $request->header('X-Razorpay-Signature');
                if (! $this->verifyRazorpaySignature($payload, $signature, $secret)) {
                    return $this->invalidSignatureResponse($provider);
                }
                break;

            default:
                // For providers without signature verification or custom implementations
                break;
        }

        // Update last activity timestamp
        $webhook->update(['last_pinged_at' => now()]);

        return $next($request);
    }

    /**
     * Verify Stripe webhook signature.
     */
    private function verifyStripeSignature(string $payload, string $signature, string $secret): bool
    {
        if (empty($signature) || empty($secret)) {
            return false;
        }

        try {
            // Extract timestamp and signatures from header
            $signatureParts = explode(',', $signature);
            $timestampPart = null;
            $signaturePart = null;

            foreach ($signatureParts as $part) {
                if (strpos($part, 't=') === 0) {
                    $timestampPart = substr($part, 2);
                } elseif (strpos($part, 'v1=') === 0) {
                    $signaturePart = substr($part, 3);
                }
            }

            if (! $timestampPart || ! $signaturePart) {
                return false;
            }

            // Compute expected signature
            $signedPayload = $timestampPart.'.'.$payload;
            $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

            return hash_equals($expectedSignature, $signaturePart);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verify Razorpay webhook signature according to official documentation
     * https://razorpay.com/docs/webhooks/validate-test/
     */
    private function verifyRazorpaySignature(string $payload, string $signature, string $secret): bool
    {
        if (empty($signature) || empty($secret)) {
            return false;
        }

        try {
            // Generate expected signature using HMAC SHA256
            $expectedSignature = hash_hmac('sha256', $payload, $secret);

            // Compare signatures using hash_equals to prevent timing attacks
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Return an invalid signature response.
     */
    private function invalidSignatureResponse(string $provider): Response
    {
        return response()->json(['error' => 'Invalid webhook signature'], 401);
    }
}
