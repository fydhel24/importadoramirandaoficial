<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
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
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if ($user && $user->status !== 'active') {
            return back()->withErrors(['email' => 'Cuenta inactiva.']);
        }

        if (Auth::attempt($credentials)) {
            return redirect()->intended($this->redirectTo);
        }

        return back()->withErrors(['email' => 'Credenciales invalidas']);
    }
}
