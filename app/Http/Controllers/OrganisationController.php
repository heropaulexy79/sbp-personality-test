<?php

namespace App\Http\Controllers;

use App\Events\EmployeeInvited;
use App\Models\Organisation;
use App\Models\OrganisationInvitation;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->organisation_id) {
            return redirect()->back(400)->with("global:message", [
                "status" => "error",
                "message" => "Account is already a member of an organisation.",
            ]);
        }

        $request->validate([
            "name" => "required|string|max:255",
        ]);

        $organisation = Organisation::create([
            "name" => $request->input("name"),
        ]);


        $user->organisation_id = $organisation->id;
        $user->save();

        return redirect()->back()->with("global:message", [
            "status" => "success",
            "message" => "Organisation has been created. You can now invite members to your organisation!",
            "action" => [
                "cta:link" => route("dashboard"),
                "cta:text" => "Invite"
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organisation $organisation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organisation $organisation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organisation $organisation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organisation $organisation)
    {
        //
    }






    public function inviteEmployee(Request $request, Organisation $organisation)
    {
        // TODO: MAKE ROLE AN ENUM
        if ($request->user()->role !== 'ADMIN') {
            return abort(401, "You don't have permission to make this request");
        }



        $request->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'nullable|in:MEMBER,ADMIN',
        ]);

        $invitation = OrganisationInvitation::create([
            'email' => $request->input('email'),
            'organization_id' => $organisation->id,
            'token' => OrganisationInvitation::generateUniqueToken(),
            'role' => $request->input('role', 'MEMBER'),
        ]);

        // Send invitation email with the unique token
        // Mail::to($invitation->email)->send(new UserInvitationEmail($invitation));

        // Send Notification
        event(new EmployeeInvited($invitation));

        return back()->with('success', 'Employee invitation sent successfully!');
    }
}
