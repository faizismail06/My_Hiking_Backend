<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


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
        $register_data->level = 1;

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

    public function loginWithGoogle(Request $request)
    {
        // Support both id_token (Android) dan access_token (Web)
        $validator = Validator::make($request->all(), [
            'id_token' => 'nullable|string',
            'access_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors(),
            ], 422);
        }

        // Ensure at least one token is provided
        if (empty($request->id_token) && empty($request->access_token)) {
            return response()->json([
                'success' => false,
                'message' => 'id_token or access_token is required',
            ], 422);
        }

        try {
            $googleData = null;
            $isIdToken = !empty($request->id_token);

            if ($isIdToken) {
                // Android: verify id_token via tokeninfo endpoint
                $googleResponse = Http::timeout(15)->get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $request->id_token,
                ]);
            } else {
                // Web: verify access_token via userinfo endpoint
                // Use withHeaders for compatibility with older Laravel versions
                $googleResponse = Http::timeout(15)->withHeaders([
                    'Authorization' => 'Bearer ' . $request->access_token,
                ])->get('https://www.googleapis.com/oauth2/v1/userinfo?alt=json');
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to validate Google token: ' . $e->getMessage(),
            ], 503);
        }

        if (!$googleResponse->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Google token',
            ], 401);
        }

        $googleData = $googleResponse->json();
        $email = strtolower($googleData['email'] ?? '');
        $isEmailVerified = ($googleData['verified_email'] ?? $googleData['email_verified'] ?? 'false') === true 
                           || ($googleData['verified_email'] ?? $googleData['email_verified'] ?? 'false') === 'true';

        if (empty($email) || !$isEmailVerified) {
            return response()->json([
                'success' => false,
                'message' => 'Google account email is not verified',
            ], 401);
        }

        // Only check audience for id_token (Android)
        // access_token doesn't have aud field
        if ($isIdToken) {
            $expectedAudience = env('GOOGLE_CLIENT_ID');
            if (!empty($expectedAudience) && ($googleData['aud'] ?? null) !== $expectedAudience) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google token audience mismatch',
                ], 401);
            }
        }

        $user = User::where('email', $email)->first();
        $isNewUser = false;

        if (!$user) {
            $isNewUser = true;
            $user = new User();
            $user->name = $googleData['name'] ?? explode('@', $email)[0];
            $user->email = $email;
            $user->password = Hash::make(Str::random(32));
            $user->level = 1;
            $user->email_verified_at = now();
            $user->save();
        } elseif (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => $isNewUser ? 'Google register successful' : 'Google login successful',
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user,
            'is_new_user' => $isNewUser,
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
            'level' => 'nullable|in:1,2,3',
            'tier' => 'prohibited',
            'tier_source' => 'prohibited',
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
        if ($request->filled('level')) {
            $user->level = (int) $request->level;
        }
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
