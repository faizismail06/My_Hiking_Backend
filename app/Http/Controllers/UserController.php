<?php

    namespace App\Http\Controllers;

    use App\Models\UserWeb;
    use Illuminate\Http\Request;

    class UserController extends Controller
    {
        /**
         * Tampilkan semua data user (id, nama, email).
         *
         * @return \Illuminate\View\View
         */
        public function index(Request $request)
        {
            // Ambil keyword pencarian dari parameter 'search'
            $search = $request->get('search');

            // Query untuk mencari data user berdasarkan name atau email
            $users = UserWeb::query()
                ->when($search, function ($query, $search) {
                    return $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->select('id', 'name', 'email')
                ->get(); // Ambil semua data setelah filter

            // Kirim data ke view 'users.index'
            return view('users.index', compact('users'));
        }

        /**
         * Tampilkan detail data user.
         *
         * @param int $id
         * @return \Illuminate\View\View
         */
        public function show($id)
        {
            // Cari data user berdasarkan id
            $user = UserWeb::findOrFail($id);

            // Kirim data ke view 'users.show'
            return view('users.show', compact('user'));
        }
        public function destroy($id)
        {
            $user = UserWeb::findOrFail($id);
            $user->delete();

            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        }
    }
