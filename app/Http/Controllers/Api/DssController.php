<?php

namespace App\Http\Controllers\Api;

use App\Models\UserDssPreference;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DssController extends Controller
{
    public function getPreferences(Request $request)
    {
        $preferences = UserDssPreference::where('user_id', $request->user()->id)
            ->pluck('weight_value', 'weight_key')
            ->toArray();

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
