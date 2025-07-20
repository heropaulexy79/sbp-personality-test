<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeadCapture;
use Illuminate\Support\Facades\Auth; // Still good practice to import, even if not used for direct user lookup
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadCaptureController extends Controller
{

    public function index(Request $request)
    {
        // Fetch all leads. You might want to add pagination or filtering later.
        $leads = LeadCapture::orderBy('created_at', 'desc')->get();

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
        ]);
    }


    /**
     * Download all captured leads as a CSV file.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads.csv"',
        ];

        $callback = function () {
            $leads = LeadCapture::orderBy('created_at', 'desc')->cursor(); // Use cursor for large datasets

            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['ID', 'Name', 'Email', 'Context', 'Captured At']);

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->id,
                    $lead->name ?? '', // Handle null names
                    $lead->email,
                    $lead->context ?? '', // Handle null context
                    $lead->created_at->toDateTimeString(),
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }


    /**
     * Store a newly captured email in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = $request->input('email');
        $context = 'personality_quiz';

        $existingCapture = LeadCapture::where('email', $email)
            ->where('context', $context)
            ->first();

        if ($existingCapture) {
            return redirect()->back()->with('message', ['status' => 'success', 'message' => 'Email saved successfully!']);
        }


        LeadCapture::create([
            'email' => $request->input('email'),
            'user_id' => null,
            'context' => 'personality_quiz',
            'metadata' => []
        ]);


        return redirect()->back()->with('message', ['status' => 'success', 'message' => 'Email saved successfully!']);
    }
}
