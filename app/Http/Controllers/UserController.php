<?php

    namespace App\Http\Controllers;

    use App\Models\UserWeb;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;

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
                ->select('id', 'name', 'email', 'level')
                ->get(); // Ambil semua data setelah filter

            // Kirim data ke view 'users.index'
            return view('users.index', compact('users'));
        }

        /**
         * Show form to create new user.
         *
         * @return \Illuminate\View\View
         */
        public function create()
        {
            return view('users.create');
        }

        /**
         * Store a newly created user.
         *
         * @param Request $request
         * @return \Illuminate\Http\RedirectResponse
         */
        public function store(Request $request)
        {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20|unique:users,phone',
                'address' => 'nullable|string|max:500',
                'level' => 'required|integer|in:1,2,3',
                'password' => 'required|string|min:6|confirmed',
            ]);

            UserWeb::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'level' => $request->level,
                'password' => Hash::make($request->password),
            ]);

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan!');
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

        /**
         * Show form to edit user.
         *
         * @param int $id
         * @return \Illuminate\View\View
         */
        public function edit($id)
        {
            $user = UserWeb::findOrFail($id);
            return view('users.edit', compact('user'));
        }

        /**
         * Update the specified user.
         *
         * @param Request $request
         * @param int $id
         * @return \Illuminate\Http\RedirectResponse
         */
        public function update(Request $request, $id)
        {
            $user = UserWeb::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20|unique:users,phone,' . $id,
                'address' => 'nullable|string|max:500',
                'level' => 'required|integer|in:1,2,3',
                'password' => 'nullable|string|min:6|confirmed',
            ]);

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'level' => $request->level,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui!');
        }

        public function destroy($id)
        {
            $user = UserWeb::findOrFail($id);
            $user->delete();

            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        }
    }
