<?php

namespace App\Http\Controllers;

use App\Models\User; // ✅ Using the User model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // ✅ Fetch Authenticated Admin Details
    public function getAdminDetails(Request $request)
    {
        $admin = Auth::user();
        return response()->json([
            'success' => true,
            'message' => 'Admin details retrieved successfully',
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => $admin->role,
        ]);
    }

    // ✅ Register New Admin (Stored in users table with role = 'admin')
    public function register(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);
        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
        ]);

        // Generate a token for the new admin
        $token = $admin->createToken('AdminApp')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Admin registered successfully',
            'admin_access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $admin = Auth::user(); // Get authenticated user
        if ($admin->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }
        $token = $admin->createToken('AdminApp')->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'Admin Login Successful',
            'admin_access_token' => $token,
            'token_type' => 'Bearer',
            'role' => 'admin',
        ]);
    }
}
