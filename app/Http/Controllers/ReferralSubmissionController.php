<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use Illuminate\Http\Request;

class ReferralSubmissionController extends Controller
{
    public function store(Request $request)
    {
        // Validate required fields only
        $validated = $request->validate([
            'last_name' => 'required|string',
            'first_name' => 'required|string',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'primary_phone' => 'required|string',
            'diagnosis' => 'required|string',
            'reason' => 'required|string',
            'gi_procedures' => 'required|array|min:1',
            'clinical_summary' => 'required|string',
        ]);

        // Create the referralz
        $referral = Referral::create([
            'status' => 'submitted',
            'workflow_id' => 1, // adjust as needed
            'form_data' => json_encode($request->except(['_token'])), // store all form data as JSON
        ]);

        return redirect()->route('referrals.thank-you');
    }
}
