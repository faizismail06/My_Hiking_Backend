<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $register_data = new User();
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'level' => '1',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors(),
            ], 422);
        }

        $register_data->name = $request->name;
        $register_data->email = $request->email;
        $register_data->password = bcrypt($request->password);
        // $register_data->level = $request->level;

        $register_data->save();

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $register_data,
        ], 201);
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Log-in failed',
                'data' => $validator->errors(),
            ], 422);
        }

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        $login_data = User::where('email', $request->email)->first();
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $login_data->createToken('auth_token')->plainTextToken,
            'user' => $login_data
        ], 200);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return response()->json([
            'success' => true,
            'message' => 'List of all users',
            'data' => $users,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Cari user berdasarkan ID
        $user = User::find($id);

        // Jika user tidak ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Return data user
        return response()->json([
            'success' => true,
            'message' => 'User details retrieved successfully',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'address' => $user->address,
                'nik' => $user->nik,
                'phone' => $user->phone,
                'emergency_phone' => $user->emergency_phone,
            ],
        ], 200);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function getUserData($id = null)
    {
        // Jika ID user tidak diberikan, ambil ID user yang sedang login
        if ($id === null) {
            $id = Auth::id();
        }

        // Cari user berdasarkan ID
        $user = User::find($id);

        // Jika user tidak ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Return semua data user
        return response()->json([
            'success' => true,
            'message' => 'User data retrieved successfully',
            'data' => $user,
        ], 200);
    }
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8',
            'address' => 'nullable|string|max:255',
            'nik' => 'nullable|numeric|unique:users,nik,' . $id,
            'phone' => 'nullable|numeric|unique:users,phone,' . $id,
            'emergency_phone' => 'nullable|numeric',
            'date_of_birth' => 'nullable|date',
            'level' => 'nullable',
            'profile_picture' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors(),
            ], 422);
        }

        $user = User::findOrFail($id);

        // Handle profile picture update
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            // Save new profile picture
            $filePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $filePath;
        }

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Update other fields
        $user->name = $request->name;
        $user->level = $request->level;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->nik = $request->nik;
        $user->phone = $request->phone;
        $user->emergency_phone = $request->emergency_phone;
        $user->date_of_birth = $request->date_of_birth;

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ], 200);
    }

    public function updatePassword(Request $request, $id)
    {
        $rules = [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|different:old_password',
            'confirm_password' => 'required|string|same:new_password',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors(),
            ], 422);
        }

        $user = User::findOrFail($id);

        // Verifikasi password lama
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Old password is incorrect',
            ], 401);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
