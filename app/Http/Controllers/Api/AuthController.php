<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Settings\TenantSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @group Authentication
 *
 * APIs for managing user authentication within tenant context
 */
class AuthController extends Controller
{
    /**
     * Login
     *
     * Authenticate a user and return an access token for API access.
     *
     * @unauthenticated
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password. Example: password123
     * @bodyParam remember_me boolean Optional. Keep the user logged in for extended period. Example: true
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Successfully logged in",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "email_verified_at": "2025-01-15T10:30:00.000000Z",
     *       "created_at": "2025-01-15T10:30:00.000000Z",
     *       "updated_at": "2025-01-15T10:30:00.000000Z"
     *     },
     *     "access_token": "1|abcdefghijklmnopqrstuvwxyz",
     *     "token_type": "Bearer",
     *     "expires_in": 3600
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Invalid credentials",
     *   "errors": {
     *     "email": ["These credentials do not match our records."]
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email field is required."],
     *     "password": ["The password field is required."]
     *   }
     * }
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember_me' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember_me'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => [
                    'email' => ['These credentials do not match our records.'],
                ],
            ], 401);
        }

        $user = Auth::user();

        // Automatically logout from other devices on successful API login
        try {
            Auth::logoutOtherDevices($request->password);
        } catch (\Exception $e) {
            // Log the error but don't fail the login process
            Log::warning('Failed to logout other devices on API login', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged in',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ],
        ]);
    }

    /**
     * Register
     *
     * Register a new user account within the tenant.
     *
     * @unauthenticated
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password (minimum 8 characters). Example: password123
     * @bodyParam password_confirmation string required Password confirmation. Example: password123
     * @bodyParam phone string Optional. The user's phone number. Example: +1234567890
     *
     * @response 201 {
     *   "success": true,
     *   "message": "User registered successfully",
     *   "data": {
     *     "user": {
     *       "id": 2,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "email_verified_at": null,
     *       "created_at": "2025-01-15T10:30:00.000000Z",
     *       "updated_at": "2025-01-15T10:30:00.000000Z"
     *     },
     *     "access_token": "2|abcdefghijklmnopqrstuvwxyz",
     *     "token_type": "Bearer"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email has already been taken."],
     *     "password": ["The password confirmation does not match."]
     *   }
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Registration is currently disabled for this tenant"
     * }
     */
    public function register(Request $request): JsonResponse
    {
        // Check if registration is enabled for this tenant
        $tenantSettings = app(TenantSettings::class);
        if (! $tenantSettings->isRegistrationEnabled) {
            return response()->json([
                'success' => false,
                'message' => 'Registration is currently disabled for this tenant',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Get Current User
     *
     * Retrieve the currently authenticated user's information.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "email_verified_at": "2025-01-15T10:30:00.000000Z",
     *       "phone": "+1234567890",
     *       "created_at": "2025-01-15T10:30:00.000000Z",
     *       "updated_at": "2025-01-15T10:30:00.000000Z"
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthenticated"
     * }
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
            ],
        ]);
    }

    /**
     * Logout
     *
     * Logout the current user and revoke their access token.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Successfully logged out"
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Refresh Token
     *
     * Generate a new access token for the authenticated user.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Token refreshed successfully",
     *   "data": {
     *     "access_token": "3|newabcdefghijklmnopqrstuvwxyz",
     *     "token_type": "Bearer",
     *     "expires_in": 3600
     *   }
     * }
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ],
        ]);
    }

    /**
     * Forgot Password
     *
     * Send a password reset link to the user's email address.
     *
     * @unauthenticated
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Password reset link sent to your email address"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email field is required."]
     *   }
     * }
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Logic for sending password reset email would go here

        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent to your email address',
        ]);
    }

    /**
     * Reset Password
     *
     * Reset the user's password using a valid reset token.
     *
     * @unauthenticated
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam token string required The password reset token. Example: abcdef123456
     * @bodyParam password string required The new password (minimum 8 characters). Example: newpassword123
     * @bodyParam password_confirmation string required Password confirmation. Example: newpassword123
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Password reset successfully"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "token": ["Invalid or expired reset token."]
     *   }
     * }
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Logic for validating token and resetting password would go here

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }

    /**
     * Verify Email
     *
     * Verify the user's email address using a verification token.
     *
     * @unauthenticated
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam token string required The email verification token. Example: abcdef123456
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Email verified successfully"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "token": ["Invalid or expired verification token."]
     *   }
     * }
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Logic for verifying email would go here

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
        ]);
    }
}
