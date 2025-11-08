<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        // Remove all logic related to Organisation invitations
        return Inertia::render('Auth/Register', [
            'prefilled' => [
                'email' => '',
            ],
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // 'course_id' => 'nullable|exists:courses,id', // <-- Removed
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // --- THIS IS THE BLOCK TO REMOVE ---
        // OrganisationUser::create([
        //     "user_id" => $user->id,
        //     "organisation_id" => 1,
        //     "role" => 'ADMIN'
        // ]);
        // --- END OF REMOVED BLOCK ---


        // --- REMOVED 'if ($request->filled('course_id'))' BLOCK ---

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}