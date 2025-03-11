<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail; // or SMS service if you're using SMS
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function index()
    {   
        $pageConfigs = ['myLayout' => 'blank'];
        return view('content.auth.login', ['pageConfigs' => $pageConfigs]);
    }

    /**
     * Handle a login request (first step: credentials).
     */
    public function login(Request $request)
    {
        // Validate the form input
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to log the user in using session-based authentication
        if (Auth::attempt($credentials)) {
            // Regenerate session to prevent session fixation
            $request->session()->regenerate();

            // Generate a random OTP, e.g. a 6-digit number
            $otp = rand(100000, 999999);

            // Store OTP and user ID in the session (or database)
            Session::put('otp', $otp);
            Session::put('otp_user_id', Auth::id());

            // Send OTP to user via email or SMS.
            // Mail::to(Auth::user()->email)->send(new OtpMail($otp));

            // For demonstration, you might log the OTP or display it on a test page.
            // \Log::info("Generated OTP: " . $otp);

            // Logout the user from the session temporarily until OTP is verified.
            Auth::logout();

            // Redirect to OTP verification page using the correct route name
            return redirect()->route('otp.verify.form');
        }

        // If authentication fails, throw a validation exception
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Show the OTP verification form.
     */
    public function showOtpForm()
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('content.auth.otp-verify', ['pageConfigs' => $pageConfigs]);
    }

    /**
     * Handle OTP verification.
     */
    // public function verifyOtp(Request $request)
    // {
    //     $request->validate([
    //         'otp' => 'required|digits:6',
    //     ]);

    //     $inputOtp = $request->input('otp');
    //     $storedOtp = Session::get('otp');
    //     $userId = Session::get('otp_user_id');

    //     if ($inputOtp == $storedOtp && $userId) {
    //         // OTP is valid. Log the user in.
    //         $user = \App\Models\User::find($userId);
    //         Auth::login($user);

    //         // Clear OTP data from session
    //         Session::forget('otp');
    //         Session::forget('otp_user_id');

    //         return redirect()->route('dashboard');
    //     }

    //     return back()->withErrors(['otp' => 'The provided OTP is invalid.']);
    // }
    public function verifyOtp(Request $request)
    {
        // Validate that an OTP (6 digits) is provided.
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        // Retrieve the user ID from session.
        $userId = Session::get('otp_user_id');

        // For development purposes, bypass OTP comparison and simply check if user exists.
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                Auth::login($user);

                // Clear OTP data from session
                Session::forget('otp');
                Session::forget('otp_user_id');

                return redirect()->route('dashboard');
            }
        }

        // If for some reason userId doesn't exist, return with an error.
        return back()->withErrors(['otp' => 'The provided OTP is invalid.']);
    }

    /**
     * Logout the user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'));
    }
}
