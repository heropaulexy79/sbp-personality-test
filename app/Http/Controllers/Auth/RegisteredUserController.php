<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OrganisationInvitation;
use App\Models\OrganisationUser;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
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

        $invitationEmail = '';

        if ($request->has('tk')) {
            $invitation = OrganisationInvitation::where('token', $request->get('tk'))->first();
            $invitationEmail = $invitation->email ?? '';
        }

        return Inertia::render('Auth/Register', [
            'prefilled' => [
                'email' => $invitationEmail,
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
            'course_id' => 'nullable|exists:courses,id', // <-- NEW
            // 'invitation_token' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        OrganisationUser::create([
            "user_id" => $user->id,
            "organisation_id" => 1,
            "role" => 'STUDENT'
        ]);


        if ($request->filled('course_id')) {
            $user->enrolledCourses()->attach($request->course_id);
        }

        event(new Registered($user));

        Auth::login($user);

        if ($request->filled('course_id')) {

            return redirect(route('dashboard', absolute: false))->with(['global:message' => [
                'status' => 'success',
                'message' => 'Successfully enrolled in the course!',
            ]], 201);
        }

        return redirect(route('dashboard', absolute: false));
    }
}
