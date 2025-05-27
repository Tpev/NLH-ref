<?php

namespace App\View\Components\Referral;

use Illuminate\View\Component;
use Illuminate\View\View;

class IntakeSidebar extends Component
{
    /** The decoded form payload */
    public array $intake;

    /**
     * @param  array  $intake
     */
    public function __construct(array $intake = [])
    {
        $this->intake = $intake;
    }

    public function render(): View
    {
        $labels = [
            'last_name'        => 'Last Name',
            'first_name'       => 'First Name',
            'dob'              => 'Date of Birth',
            'gender'           => 'Gender',
            'primary_phone'    => 'Primary Phone',
            'height'           => 'Height (in)',
            'weight'           => 'Weight (lbs)',
            'emergency_contact'=> 'Emergency Contact',
            'emergency_phone'  => 'Emergency Phone',
            'interpreter'      => 'Interpreter',
            'insurance_plan'   => 'Insurance Plan',
            'auth_number'      => 'Auth / Referral #',
            'referring_physician' => 'Referring Provider',
            'referring_facility'  => 'Facility',
            'referring_phone'     => 'Referring Phone',
            'referring_fax'       => 'Referring Fax',
            'referring_npi'       => 'NPI',
            'reason'           => 'Reason for Referral',
            'diagnosis'        => 'Diagnosis / ICD',
            'gi_procedures'    => 'GI Procedures',
            'clinical_summary' => 'Clinical Summary',
        ];

        return view('components.referral.intake-sidebar', [
            'labels' => $labels,
            'intake' => $this->intake,
        ]);
    }
}
