<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailOtp;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors(),
            ], 422);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->level = 1;
        $user->email_verified_at = null; // Memastikan email belum terverifikasi di awal
        $user->save();

        // Generasi Kode OTP 6-Digit
        $otpCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Simpan Record OTP
        EmailOtp::create([
            'email' => $user->email,
            'otp_code' => $otpCode,
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
        ]);

        // Kirim Email OTP ke Alamat Email Pendaftar
        $emailSent = false;
        try {
            Mail::to($user->email)->send(new OtpMail($otpCode, $user->name));
            $emailSent = true;
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil! Kode OTP verifikasi telah dikirimkan ke email Anda.',
            'email' => $user->email,
            'email_sent' => $emailSent,
            'data' => $user,
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors(),
            ], 422);
        }

        $otpRecord = EmailOtp::where('email', $request->email)
            ->where('otp_code', $request->otp_code)
            ->where('is_used', false)
            ->where('expires_at', '>=', now())
            ->orderBy('id', 'desc')
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah atau sudah kadaluwarsa (berlaku 10 menit).',
            ], 422);
        }

        // Tandai OTP sebagai digunakan
        $otpRecord->update(['is_used' => true]);

        // Tandai User email terverifikasi
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi email berhasil! Selamat datang di MyHiking.',
                'token' => $token,
                'user' => $user,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'User tidak ditemukan.',
        ], 444);
    }

    public function resendOtp(Request $request)
    {
        $rules = [
            'email' => 'required|email|exists:users,email',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar.',
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email ini sudah terverifikasi sebelumnya.',
            ], 400);
        }

        // Invalidate old OTPs
        EmailOtp::where('email', $user->email)->update(['is_used' => true]);

        // Generate new OTP
        $otpCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        EmailOtp::create([
            'email' => $user->email,
            'otp_code' => $otpCode,
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
        ]);

        $emailSent = false;
        try {
            Mail::to($user->email)->send(new OtpMail($otpCode, $user->name));
            $emailSent = true;
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP baru telah dikirimkan ke email Anda.',
            'email_sent' => $emailSent,
        ], 200);
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
            'address' => 'nullable|string|max:500',
            'nik' => 'nullable|string|max:20|unique:users,nik,' . $id,
            'phone' => 'nullable|string|max:250|unique:users,phone,' . $id,
            'emergency_phone' => 'nullable|string|max:250',
            'date_of_birth' => 'nullable|date',
            'level' => 'nullable|in:1,2,3',
            'tier' => 'prohibited',
            'tier_source' => 'prohibited',
            'profile_picture' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
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

    public function uploadFaceVerification(Request $request)
    {
        $rules = [
            'face_photo' => 'required|file|mimes:jpeg,png,jpg|max:5120',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if ($user->face_photo_path) {
            Storage::disk('public')->delete($user->face_photo_path);
        }

        $filePath = $request->file('face_photo')->store('face_verifications', 'public');

        $user->face_photo_path = $filePath;
        $user->is_face_verified = true;
        $user->face_verified_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Face verification uploaded and saved successfully to MySQL',
            'data' => [
                'user_id' => $user->id,
                'face_photo_path' => $filePath,
                'is_face_verified' => true,
                'face_verified_at' => $user->face_verified_at,
            ],
        ], 200);
    }

    public function sendPhoneOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor HP wajib diisi.',
                'data' => $validator->errors(),
            ], 422);
        }

        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        if (substr($phone, 0, 2) === '08') {
            $targetPhone = '62' . substr($phone, 1);
        } else {
            $targetPhone = $phone;
        }

        $otpCode = (string) rand(1000, 9999);
        \Illuminate\Support\Facades\Cache::put('phone_otp_' . $targetPhone, $otpCode, now()->addMinutes(5));

        $fonnteToken = env('FONNTE_TOKEN');
        $isSentViaWa = false;
        $waMessage = "📱 *KODE OTP VERIFIKASI MYHIKING*\n\nKode OTP Anda adalah: *$otpCode*\n\nJangan berikan kode ini kepada siapa pun demi keamanan akun pendakian Anda. Kode berlaku 5 menit.";

        if (!empty($fonnteToken)) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => $fonnteToken,
                ])->post('https://api.fonnte.com/send', [
                    'target' => $targetPhone,
                    'message' => $waMessage,
                ]);

                if ($response->successful()) {
                    $isSentViaWa = true;
                }
            } catch (\Throwable $e) {
                Log::error('Fonnte WA API Error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => $isSentViaWa 
                ? 'Kode OTP WhatsApp berhasil dikirimkan ke nomor HP Anda! 💬' 
                : 'Kode OTP berhasil dibuat (Mode Simulasi WA).',
            'is_sent_via_wa' => $isSentViaWa,
            'otp_debug' => $otpCode,
        ], 200);
    }

    public function verifyPhoneOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor HP dan Kode OTP wajib diisi.',
            ], 422);
        }

        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        if (substr($phone, 0, 2) === '08') {
            $targetPhone = '62' . substr($phone, 1);
        } else {
            $targetPhone = $phone;
        }

        $cachedOtp = \Illuminate\Support\Facades\Cache::get('phone_otp_' . $targetPhone);

        if (!$cachedOtp || $cachedOtp !== trim($request->otp_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah atau telah kadaluarsa. Silakan minta kode baru.',
            ], 400);
        }

        \Illuminate\Support\Facades\Cache::forget('phone_otp_' . $targetPhone);

        $user = Auth::user() ?? User::where('phone', $request->phone)->first();
        if ($user) {
            $user->phone = $request->phone;
            $user->is_phone_verified = true;
            $user->phone_verified_at = now();
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Nomor telepon pendaki berhasil diverifikasi! ✅',
            'data' => [
                'is_phone_verified' => true,
                'phone_verified_at' => now(),
            ],
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
