<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
class RegisterController extends Controller
{
    public function create()
    {
        return view('session.register');
    }

    public function store()
    {
        $attributes = request()->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users', 'email')],
            'password' => ['required', 'min:5', 'max:20'],
            'agreement' => ['accepted']
        ]);

        $plainPassword = $attributes['password'];

        $attributes['password'] = bcrypt($attributes['password']);
    
        $user = User::create($attributes);
    
        Mail::to($user->email)->send(new WelcomeEmail($user,$plainPassword));
    
        session()->flash('success', 'Your account has been created. Check your email for login credentials.');
        return redirect('/login');
    }
}
