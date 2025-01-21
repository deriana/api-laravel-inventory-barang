<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'user_nama' => 'required|string|max:50',
                'user_email' => 'required|string|email|max:100|unique:tm_user,user_email',
                'password'  => 'required|string', 
                'user_hak'  => 'required|string|max:2',
            ]);
            
            $latestUser = User::latest('user_id')->first(); 
            $userId = 'USR' . str_pad((intval(substr($latestUser->user_id ?? 'USER000', 4)) + 1), 3, '0', STR_PAD_LEFT);

            $user = User::create([
                'user_id'   => $userId,  
                'user_nama' => $data['user_nama'],
                'user_email' => $data['user_email'],
                'user_pass' => Hash::make($data['password']),  
                'user_hak'  => $data['user_hak'],
                'user_sts'  => '1', 
            ]);

            $token = $user->createToken('user-token')->plainTextToken;

            $response = [
                'user'  => $user->makeHidden(['user_pass', 'remember_token']),
                'token' => $token,
            ];

            return response()->json($response, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => $e->errors() 
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage() 
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'user_email' => 'required|string|email',
                'user_pass'  => 'required|string',
            ]);

            $user = User::where('user_email', $data['user_email'])->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            if (!Hash::check($data['user_pass'], $user->user_pass)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $token = $user->createToken('user-token')->plainTextToken;

            return response()->json([
                'user'  => $user->makeHidden(['user_pass', 'remember_token']),
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error', 'details' => $e->getMessage()], 500);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $user = Auth::user();

            $user->tokens->each(function ($token) {
                $token->delete();
            });

            return response()->json([
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Logout Error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request, $user_id): JsonResponse
    {
        try {
            if (!$request->hasHeader('Authorization')) {
                return response()->json(['error' => 'Token not provided. Please provide a valid token.'], 401);
            }
    
            $user = User::find($user_id); 
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
    
            $data = $request->validate([
                'user_nama' => 'required|string|max:50',
                'user_email' => 'required|email|max:100|unique:tm_user,user_email,' . $user->user_id . ',user_id',
                'user_pass' => 'nullable|string',
            ]);
    
            $user->user_nama = $data['user_nama'];
            $user->user_email = $data['user_email'];
    
            if (!empty($data['user_pass'])) {
                $user->user_pass = Hash::make($data['user_pass']);
            }
    
            $user->save(); 
    
            return response()->json(['message' => 'Account updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500); // Menampilkan pesan error jika terjadi kesalahan
        }
    }
    public function changeRole(Request $request, $user_id): JsonResponse
    {
        try {
            if (!$request->hasHeader('Authorization')) {
                return response()->json(['error' => 'Token not provided. Please provide a valid token.'], 401);
            }
        
            $targetUser = User::find($user_id);
            if (!$targetUser) {
                return response()->json(['error' => 'Target user not found.'], 404);
            }
    
            $data = $request->validate([
                'new_role' => 'required|string|max:2', 
            ]);
    
            $allowedRoles = ['01', '02', '03']; 
            if (!in_array($data['new_role'], $allowedRoles)) {
                return response()->json(['error' => 'Invalid role. Allowed roles: ' . implode(', ', $allowedRoles)], 400);
            }
    
            $targetUser->user_hak = $data['new_role'];
            $targetUser->save();
    
            return response()->json([
                'message' => 'Role updated successfully',
                'user' => $targetUser->makeHidden(['user_pass', 'remember_token']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error', 'message' => $e->getMessage()], 500);
        }
    }
    
}
