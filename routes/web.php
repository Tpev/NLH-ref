<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ReferralsIndex;
use App\Livewire\ReferralWorkflowShow;
use App\Livewire\Dashboard;
use App\Livewire\Referrals\GiBookingFormComponent;
use App\Http\Controllers\ReferralSubmissionController;

Route::post('/referrals/gi/store', [ReferralSubmissionController::class, 'store'])->name('referral.store');


Route::get('/test-nlp', function () {
    $text = <<<EOT
Patient 3: Caleb Johnson – Pre-operative Clearance Note
Date: 27 May 2025 – Author: PCP

Chief Reason for Visit
Clearance for arthroscopic right rotator-cuff repair (planned outpatient general anesthesia with interscalene block).

History of Present Illness
63-year-old male with chronic right-shoulder pain refractory to PT and injections; surgery scheduled in 4 weeks.

Past Medical History

Moderate aortic stenosis (valve area 1.3 cm², mean gradient 22 mmHg) – last echo 01 Nov 2024.

Mild pulmonary hypertension (PASP 40 mmHg).

Obstructive sleep apnea—CPAP compliant (>6 h/night download reviewed).

Type 2 diabetes mellitus—fair control, HbA1c 8.0 % (Mar 2025).

Hyperlipidemia, well controlled.

Medications / Allergies
Metformin 1000 mg BID, Lisinopril 10 mg QD, Atorvastatin 40 mg QD. No allergies.

Social / Functional
BMI 49 kg/m² (weight 148 kg, height 1.74 m). Ambulates unaided; plays 9-hole golf walking (METs ≈ 5). Never smoker. Wife available full-time for 1 week post-op.

Review of Systems
Denies exertional chest pain, syncope, or PND. Mild exertional dyspnea on hills.

Physical Exam
BP 130/80 mmHg; HR 74; SpO₂ 95 % RA. Grade 2/6 mid-systolic murmur at RUSB without radiation. Lungs clear. Neck ROM full; Mallampati II. Right shoulder limited abduction with crepitus.

Investigations
Echo 01 Nov 2024 reviewed above—no change in symptoms since. CMP/CBC WNL.
EOT;

    $response = Http::post('http://localhost:8001/analyze', [
        'text' => $text,
    ]);

    return response()->json($response->json());
});




Route::get('/referrals/gi/new', GiBookingFormComponent::class)
    ->middleware(['auth'])->name('referral-create');




// routes/web.php
Route::get('/dashboard', function() {
    return view('dashboard-page');
})->name('dashboard');


// Redirect root route to login
Route::redirect('/', '/login');

// Protected Routes (Only accessible by authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('/med-reconciliation', 'med-reconciliation')->name('med-reconciliation');

    Route::get('/patient-timeline', function () {
        return view('patient-timeline');
    })->name('patient-timeline');
    Route::get('/patient-view', function () {
        return view('patient-view');
    })->name('patient-view');    
	Route::get('/visit', function () {
        return view('visit');
    })->name('visit');

    Route::view('/discharges', 'referrals.index')->name('referrals.index');

    Route::get('/discharges/{id}/workflow', function ($id) {
        return view('referrals.workflow-show', ['id' => $id]);
    })->name('referrals.workflow.show');


});

// Jetstream / Sanctum Auth Routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Additional Jetstream-specific routes if needed
});
