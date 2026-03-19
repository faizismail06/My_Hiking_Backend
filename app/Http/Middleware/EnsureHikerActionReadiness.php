<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\UserActionReadinessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHikerActionReadiness
{
    public function __construct(private UserActionReadinessService $readinessService)
    {
    }

    /**
     * Validasi berlapis sebelum user menjalankan aksi penting.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = $request->user();
        $targetUserId = $request->input('id_user') ?? $request->input('user_id') ?? $authUser?->id;

        if (!$targetUserId) {
            return response()->json([
                'success' => false,
                'code' => 'USER_CONTEXT_REQUIRED',
                'message' => 'User context wajib untuk aksi ini.',
            ], 401);
        }

        if ($authUser && (string) $targetUserId !== (string) $authUser->id) {
            return response()->json([
                'success' => false,
                'code' => 'FORBIDDEN_USER_CONTEXT',
                'message' => 'Aksi hanya boleh dilakukan untuk akun yang sedang login.',
            ], 403);
        }

        $user = $authUser ?: User::find($targetUserId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'code' => 'USER_NOT_FOUND',
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        // Tier hanya relevan untuk user pendaki aktif (level 1).
        if ((int) $user->level !== 1) {
            return $next($request);
        }

        $missingIdentity = $this->readinessService->missingIdentityFields($user);
        if (!empty($missingIdentity)) {
            return response()->json([
                'success' => false,
                'code' => 'PROFILE_INCOMPLETE',
                'message' => 'Data identitas belum lengkap. Lengkapi profil terlebih dahulu.',
                'next_step' => 'profile_screen',
                'missing_fields' => $missingIdentity,
            ], 409);
        }

        if ($this->readinessService->needsTierOnboarding($user)) {
            return response()->json([
                'success' => false,
                'code' => 'EXPERIENCE_ONBOARDING_REQUIRED',
                'message' => 'Data pengalaman pendaki belum diisi. Selesaikan onboarding experience terlebih dahulu.',
                'next_step' => 'experience_onboarding',
            ], 409);
        }

        return $next($request);
    }
}
