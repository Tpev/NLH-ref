<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ReferralsIndex;
use App\Livewire\ReferralWorkflowShow;
use App\Livewire\Dashboard;
use App\Livewire\Referrals\GiBookingFormComponent;
use App\Http\Controllers\ReferralSubmissionController;
use App\Http\Controllers\ReferralReportController;



Route::post('/referrals/gi/store', [ReferralSubmissionController::class, 'store'])->name('referral.store');



Route::get('/referrals/thank-you', function () {
    return view('referrals.thank-you');
})->name('referrals.thank-you');   // give it a name so Livewire can redirect easily



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
   Route::get('/reports/referrals', [ReferralReportController::class, 'index'])->name('reports.referrals');
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
