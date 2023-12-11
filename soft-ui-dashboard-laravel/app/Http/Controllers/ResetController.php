<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ResetController extends Controller
{
    public function create()
    {
        return view('session/reset-password/sendEmail');
    }

    public function sendEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        $token = Str::random(60);
        $expires_at = now()->addMinutes(30);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
                'expires_at' => $expires_at,
                'used' => false,
            ]
        );

        Mail::to($user->email)->send(new ResetPasswordEmail($token));

        return back()->with('success', 'Password reset link sent successfully. Check your email.');
    }

    public function resetPass(Request $request, $token = null)
    {
        $resetData = DB::table('password_resets')
            ->where('token', $token) 
            ->where('used', false)
            ->first();

        if (!$resetData || Carbon::parse($resetData->expires_at)->isPast()) {
            return redirect('/login')->with('danger', 'This password reset link is invalid or has expired.');
        }
        
        DB::table('password_resets')->where('token', $token)->update(['used' => true]);

        return view('session.reset-password.resetPassword')->with(
            ['token' => $token, 'email' => $resetData->email]
        );
    }

}
