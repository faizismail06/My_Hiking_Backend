<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Mountain;
use Illuminate\Http\Request;

class MountainController extends Controller
{
    public function index()
    {
        $mountainList = Mountain::with('province')->get();

        // Format response
        $result = $mountainList->map(function ($mountain) {
            $imageUrl = url('api/images/' . $mountain->gambar_gunung);

            return [
                'id' => $mountain->id,
                'nama' => $mountain->nama,
                'gambar' => $imageUrl,
                'province' => $mountain->province ? ['id' => $mountain->province->id, 'name' => $mountain->province->name] : null,
            ];
        });

        return response()->json($result);
    }
}
