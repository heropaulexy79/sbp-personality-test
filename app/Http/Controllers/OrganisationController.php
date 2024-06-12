<?php

namespace App\Http\Controllers;

use App\Events\EmployeeInvited;
use App\Models\Organisation;
use App\Models\OrganisationInvitation;
use App\Models\OrganisationUser;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        // $user->organisation_id = $user->organisationNew

        if ($user->organisationNew->organisation_id) {
            return redirect()->back(400)->with('global:message', [
                'status' => 'error',
                'message' => 'Account is already a member of an organisation.',
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $organisation = Organisation::create([
            'name' => $request->input('name'),
        ]);


        $organisation = OrganisationUser::create([
            // 'name' => $request->input('name'),
            'user_id' => $user->id,
            'organisation_id' => $organisation->id,
            'role' => User::ROLE_ADMIN,
        ]);

        return redirect()->back()->with('global:message', [
            'status' => 'success',
            'message' => 'Organisation has been created. You can now invite members to your organisation!',
            'action' => [
                'cta:link' => route('organisation.edit'),
                'cta:text' => 'Invite',
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organisation $organisation)
    {
        //
    }

    public function getAllEmployees(Request $request)
    {
        //
        $user = $request->user();


        return response([
            "students" => $user->organisationNew->organisation->employees()->with("user:id,name,email")->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdminInOrganisation($user->organisationNew->organisation)) {
            return abort(404);
        }

        return Inertia::render('Organisation/Edit', [
            'organisation' => $user->organisationNew->organisation,
            'employees' => $user->organisationNew->organisation->employees()->with("user:id,name,email")->get(),
            'invites' => $user->organisationNew->organisation->invites,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organisation $organisation)
    {
        //
        $user = $request->user();

        if (!$user->isAdminInOrganisation($organisation)) {
            return abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $organisation->name = $request->name;

        $organisation->save();

        return redirect()->back()->with('global:message', [
            'status' => 'success',
            'message' => 'Changes have been saved!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organisation $organisation)
    {
        //
    }

    public function updateEmployee(Request $request, Organisation $organisation, User $employee)
    {

        $user = $request->user();

        if (!$user->isAdminInOrganisation($user->organisationNew->organisation)) {
            return abort(401, "You don't have permission to make this request");
        }

        $request->validate([
            'role' => 'required|in:STUDENT,ADMIN',
        ]);

        if (!$employee || $employee->organisation_id !== $organisation->id) {
            return back()->with('global:message', [
                'status' => 'error',
                'message' => 'Employee not found!',
            ]);
        }

        $employee->role = $request->role;
        $employee->save();

        return back();
    }

    public function inviteEmployees(Request $request, Organisation $organisation)
    {

        $user = $request->user();

        if (!$user->isAdminInOrganisation($user->organisationNew->organisation)) {
            return abort(401, "You don't have permission to make this request");
        }

        $request->validate([
            // 'email' => '',
            'invites' => 'required|array|min:1',
            'invites.*.email' => 'required|email|unique:users,email'
        ]);


        $invites = [];



        foreach ($request->input("invites") as $key => $value) {

            $invites[] = [
                'email' => $value["email"],
                'organisation_id' => $organisation->id,
                'token' => OrganisationInvitation::generateUniqueToken(),
                'role' => $request->input('role', 'STUDENT'),
            ];
        }



        OrganisationInvitation::insert($invites);

        $invitesEmails = array_column($invites, 'email');
        $invitations = OrganisationInvitation::whereIn('email', $invitesEmails)->get();

        // dd($invitations);

        // foreach ($invitations as $invitation) {
        //     // Send Notification
        //     event(new EmployeeInvited($invitation));
        // }


        return back()->with('success', 'Employee invitations sent successfully!');
    }
    public function inviteEmployee(Request $request, Organisation $organisation)
    {

        $user = $request->user();

        if (!$user->isAdminInOrganisation($user->organisationNew->organisation)) {
            return abort(401, "You don't have permission to make this request");
        }

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'nullable|in:STUDENT,ADMIN',
        ]);

        $invitation = OrganisationInvitation::create([
            'email' => $request->input('email'),
            'organisation_id' => $organisation->id,
            'token' => OrganisationInvitation::generateUniqueToken(),
            'role' => $request->input('role', 'STUDENT'),
        ]);

        // Send Notification
        event(new EmployeeInvited($invitation));

        return back()->with('success', 'Employee invitation sent successfully!');
    }

    public function uninviteEmployee(Request $request, Organisation $organisation, OrganisationInvitation $invitation)
    {

        $user = $request->user();

        if (!$user->isAdminInOrganisation($user->organisationNew->organisation)) {
            return abort(401, "You don't have permission to make this request");
        }

        $invitation->delete();

        // "global:message", [
        //     "status" => "success",
        //     "message" => "Uninvited",
        // ]
        return back();
    }
}
