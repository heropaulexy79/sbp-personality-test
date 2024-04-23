<?php

namespace App\Http\Controllers;

use App\Models\BillingHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $org = $request->user()->organisation;

        return Inertia::render('Organisation/Billing/Index', [
            "payment_method" => $org->paymentMethods->first(),
            "history" => $org->billingHistories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        //
    }


    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, BillingHistory $billingHistory)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(BillingHistory $billingHistory)
    // {
    //     //
    // }
}
