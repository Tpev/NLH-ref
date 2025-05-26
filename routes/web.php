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
The patient is a 67-year-old male with multiple chronic conditions. He has a history of type 2 diabetes, poorly controlled, with a recent hemoglobin A1C of 9.2. He is also diagnosed with congestive heart failure, with an ejection fraction of 25% documented in the last echocardiogram. He was treated for a stroke approximately 6 weeks ago and has residual left-sided weakness.

Pulmonary history includes COPD and oxygen dependency at home, with baseline resting SpO2 at 91%.

Patient has stage 4 chronic kidney disease and is not currently on dialysis, though nephrology follow-up is ongoing. He also has a BMI of 53, is non-ambulatory, and requires a Hoyer lift for transfers. His mobility limitations are complicated by a diagnosis of ankylosing spondylitis, leading to a limited range of motion in the neck and spine.
EOT;

    $response = Http::post('http://localhost:8001/analyze', [
        'text' => $text,
    ]);

    return response()->json($response->json());
});




Route::get('/referrals/gi/new', GiBookingFormComponent::class)
    ->middleware(['auth']);




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
