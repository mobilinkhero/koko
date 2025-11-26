<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInputs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge($this->sanitizeInput($request->all()));

        return $next($request);
    }

    /**
     * Recursively trim and normalize whitespace in an array.
     */
    private function sanitizeInput(array $data): array
    {
        foreach ($data as $key => $value) {
            // If array, recurse
            if (is_array($value)) {
                $data[$key] = $this->sanitizeInput($value);

                continue;
            }

            if ($key === 'reply_text' || $key === 'replyContent' || $key === 'content' || $key === 'body') {
                continue;
            }

            if (is_string($value)) {
                $data[$key] = preg_replace('/\s+/', ' ', trim($value));
            }
        }

        return $data;
    }
}
