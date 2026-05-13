<?php

namespace App\Http\Controllers\Api;

use App\Models\UserDssPreference;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DssController extends Controller
{
    public function getPreferences(Request $request)
    {
        $savedPrefs = UserDssPreference::where('user_id', $request->user()->id)
            ->pluck('weight_value', 'weight_key')
            ->toArray();

        // Semua weight keys yang digunakan oleh DSS preference screen.
        // Jika user belum pernah menyimpan (pertama kali login / fresh user),
        // kembalikan default 3.0 agar frontend memiliki data lengkap.
        $defaultKeys = [
            'priority_cost',
            'priority_distance',
            'priority_duration',
            'priority_difficulty',
            'priority_elevation',
            'priority_panorama',
            'priority_fasilitas',
            'priority_crowd_level',
            'priority_popularity',
            'priority_safety',
        ];

        $preferences = [];
        foreach ($defaultKeys as $key) {
            $preferences[$key] = isset($savedPrefs[$key])
                ? (float) $savedPrefs[$key]
                : 3.0;
        }

        return response()->json([
            'success' => true,
            'preferences' => $preferences,
        ]);
    }

    public function savePreferences(Request $request)
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*' => 'numeric|between:1,5',
        ]);

        $userId = $request->user()->id;

        foreach ($validated['preferences'] as $key => $value) {
            UserDssPreference::updateOrCreate(
                ['user_id' => $userId, 'weight_key' => $key],
                ['weight_value' => $value]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Preferences saved',
        ]);
    }
}
