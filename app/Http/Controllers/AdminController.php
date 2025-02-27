<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // ✅ Fetch Authenticated Admin Details
    public function getAdminDetails(Request $request)
    {
        $admin = Auth::guard('sanctum')->user(); // ✅ Use this instead of `Auth::user()`

        if (!$admin || get_class($admin) !== Admin::class) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Admin details retrieved successfully',
            'name' => $admin->name,
            'email' => $admin->email,

        ]);
    }


    // ✅ Register New Admin
    public function register(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|min:8|confirmed', // Ensure passwords match
        ]);

        // Create new admin
        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // ✅ Hash password before storing
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

    // ✅ Admin Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        // ✅ Ensure login is done using the `admin` guard
        if (!Auth::guard('admin')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $admin = Auth::guard('admin')->user();
        $token = $admin->createToken('AdminApp')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Admin Login Successful',
            'admin_access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
