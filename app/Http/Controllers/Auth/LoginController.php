<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');

        // Protect /login route using a secret key parameter
        $this->middleware(function ($request, $next) {
            // Authorize session if correct key is provided in GET query parameter
            if ($request->isMethod('get') && $request->query('key') === 'myhiking') {
                session(['can_access_login' => true]);
            }

            // Abort with 404 if session is not authorized to access login
            if (!session('can_access_login')) {
                abort(404);
            }

            return $next($request);
        })->only(['showLoginForm', 'login']);
    }

    protected function redirectTo()
    {
        session()->flash('success', 'You are logged in!');

        // Redirect berdasarkan level user
        $user = Auth::user();

        if ($user->level == 2) {
            // Penjaga jalur ke dashboard penjaga
            return route('guards.dashboard');
        } elseif ($user->level == 3) {
            // Admin ke dashboard admin
            return route('home');
        }

        // User biasa (level 1) atau lainnya
        return $this->redirectTo;
    }
}
