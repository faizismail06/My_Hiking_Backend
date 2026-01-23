<?php

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use app\http\Controllers\Controller;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Validasi file gambar
        ]);

        // Jika ada file gambar yang diunggah
        if ($request->hasFile('profile_picture')) {
            // Simpan file di folder `public/profile_pictures`
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $validatedData['profile_picture'] = $path;
        }

        // Buat user dengan data yang sudah divalidasi
        User::create($validatedData);

        return redirect()->back()->with('success', 'User created successfully');
    }
}
