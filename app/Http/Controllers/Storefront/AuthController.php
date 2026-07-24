<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:customers,email',
            'password' => 'required|string|min:6|confirmed',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::create([
            'name' => $request->name ?? explode('@', $request->email)[0],
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'locale' => app()->getLocale(),
            'type' => 'customer',
        ]);

        $token = $customer->createToken('storefront-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $customer->only(['id', 'name', 'email', 'phone']),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        $token = $customer->createToken('storefront-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $customer->only(['id', 'name', 'email', 'phone']),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function user(Request $request): JsonResponse
    {
        $customer = $request->user()->load('addresses');

        return response()->json([
            'user' => $customer->only(['id', 'name', 'email', 'phone', 'locale']),
            'addresses' => $customer->addresses,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer->update($request->only(['name', 'email', 'phone']));

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $customer->fresh()->only(['id', 'name', 'email', 'phone']),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $customer->password)) {
            return response()->json([
                'errors' => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        $customer->update(['password' => $request->password]);

        return response()->json(['message' => 'Password updated successfully.']);
    }
}
