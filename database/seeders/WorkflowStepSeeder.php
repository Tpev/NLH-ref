<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkflowStage;
use App\Models\WorkflowStep;
use App\Models\Workflow;

class WorkflowStepSeeder extends Seeder
{
    public function run()
    {
        $workflow = Workflow::where('name', 'Patient Discharge Workflow')->first();
        if (!$workflow) {
            $this->command->error('Workflow "Patient Discharge Workflow" not found.');
            return;
        }

        $exclusionStage = WorkflowStage::firstOrCreate(
            [
                'name' => 'Patient Exclusion Criteria',
                'workflow_id' => $workflow->id,
            ],
            [
                'description' => 'Evaluation of patient eligibility for outpatient procedures'
            ]
        );

        $steps = [

            [
                'name' => 'Exclusion Criteria Form',
                'type' => 'form',
                'order' => 2,
                'metadata' => [
                    'fields' => [
    ['name' => 'cns_dementia', 'type' => 'checkbox', 'label' => 'CNS: Moderate to severe dementia'],
    ['name' => 'cns_stroke', 'type' => 'checkbox', 'label' => 'CNS: Stroke or TIA within last 3 months'],
    ['name' => 'cns_other_disorders', 'type' => 'checkbox', 'label' => 'CNS: ALS, MS, Parkinson’s or other with functional limitation'],

    ['name' => 'cardiac_stent_des', 'type' => 'checkbox', 'label' => 'Cardiac: Drug-eluting stent <6 months'],
    ['name' => 'cardiac_stent_bms', 'type' => 'checkbox', 'label' => 'Cardiac: Bare metal stent <30 days'],
    ['name' => 'cardiac_angina', 'type' => 'checkbox', 'label' => 'Cardiac: Unstable angina'],
    ['name' => 'cardiac_hcm', 'type' => 'checkbox', 'label' => 'Cardiac: Severe hypertrophic cardiomyopathy'],
    ['name' => 'cardiac_phtn', 'type' => 'checkbox', 'label' => 'Cardiac: Severe pulmonary hypertension'],
    ['name' => 'cardiac_chf', 'type' => 'checkbox', 'label' => 'Cardiac: CHF with EF <30%'],

    ['name' => 'pulm_o2', 'type' => 'checkbox', 'label' => 'Pulmonary: Oxygen dependency (not just nighttime)'],
    ['name' => 'pulm_sats', 'type' => 'checkbox', 'label' => 'Pulmonary: Resting sats <92%'],
    ['name' => 'pulm_asthma', 'type' => 'checkbox', 'label' => 'Pulmonary: Asthma/COPD exacerbation past 30 days'],
    ['name' => 'pulm_cpaps', 'type' => 'checkbox', 'label' => 'Pulmonary: CPAP/BiPAP non-compliance'],
    ['name' => 'pulm_trach', 'type' => 'checkbox', 'label' => 'Pulmonary: Tracheostomy present'],

    ['name' => 'endocrine_a1c', 'type' => 'checkbox', 'label' => 'Endocrine: HgbA1c >8.5'],

    ['name' => 'pregnancy', 'type' => 'checkbox', 'label' => 'Pregnancy: Currently pregnant'],

    ['name' => 'renal_dialysis', 'type' => 'checkbox', 'label' => 'Renal: Dialysis-dependent'],

    ['name' => 'liver_cirrhosis', 'type' => 'checkbox', 'label' => 'Liver: End-stage cirrhosis'],

    ['name' => 'coag_thrombocytopenia', 'type' => 'checkbox', 'label' => 'Coagulopathy: Platelets <75k'],
    ['name' => 'coag_cannot_stop', 'type' => 'checkbox', 'label' => 'Coagulopathy: Cannot stop anticoagulation'],
    ['name' => 'coag_disorders', 'type' => 'checkbox', 'label' => 'Coagulopathy: von Willebrand, Factor deficiency, etc.'],
    ['name' => 'coag_dvt_pe', 'type' => 'checkbox', 'label' => 'Coagulopathy: Current DVT/PE on anticoagulation'],

    ['name' => 'anesthesia_delirium', 'type' => 'checkbox', 'label' => 'Anesthesia: History of post-op delirium'],
    ['name' => 'anesthesia_mh', 'type' => 'checkbox', 'label' => 'Anesthesia: (Family) History of malignant hyperthermia'],
    ['name' => 'anesthesia_airway', 'type' => 'checkbox', 'label' => 'Anesthesia: Known difficult airway'],
    ['name' => 'anesthesia_msk', 'type' => 'checkbox', 'label' => 'Anesthesia: Limited neck ROM (RA, AS, etc.)'],

    ['name' => 'pediatrics_age', 'type' => 'checkbox', 'label' => 'Pediatrics: Age <12 months or tonsillectomy <3 yrs'],
    ['name' => 'pediatrics_genetic', 'type' => 'checkbox', 'label' => 'Pediatrics: Congenital/complex comorbidities'],

    ['name' => 'bmi_gt50', 'type' => 'checkbox', 'label' => 'BMI: >50'],
    ['name' => 'bmi_gt45_abd', 'type' => 'checkbox', 'label' => 'BMI: >45 for intra-abdominal case'],
    ['name' => 'bmi_40_50_eval', 'type' => 'checkbox', 'label' => 'BMI: 40–50 requires anesthesia evaluation'],

    ['name' => 'opioid_suboxone', 'type' => 'checkbox', 'label' => 'Opioids: Suboxone or chronic opioid use'],

    ['name' => 'mobility_hoyer', 'type' => 'checkbox', 'label' => 'Mobility: Non-ambulatory or needs Hoyer lift'],


                    ]
                ],
                'group_can_write' => ['anesthesiologist', 'admin'],
                'group_can_see' => ['anesthesiologist', 'surgeon'],
                'group_get_notif' => ['surgical_team']
            ],
/*             [
                'name' => 'Notify Anesthesia Member',
                'type' => 'notify',
                'order' => 3,
                'metadata' => [
                    'label' => 'Notify Family about discharge',
                ],
                'group_can_write' => ['social_worker', 'nurse'],
                'group_can_see' => ['social_worker', 'nurse'],
                'group_get_notif' => [],
            ], */
			[
            'name'              => 'Notify Anesthesia?',
            'type'              => 'decision',
            'order'             => 3,
            'metadata'          => [
                'question' => 'Notify Anesthesia?',
                'options'  => ['Yes', 'No'],
                // You can define on_true/on_false or leave as-is
                'on_true'  => 'Continue discharge workflow',
                'on_false' => 'No discharge needed',
            ],
            'group_can_write'   => ['discharge_coordinator','social_worker'],
            'group_can_see'     => ['discharge_coordinator','social_worker'],
            'group_get_notif'   => ['pt_ot','nurse','provider','social_worker','family'],
],
[
            'name'              => 'Send patient to scheduling?',
            'type'              => 'decision',
            'order'             => 4,
            'metadata'          => [
                'question' => 'Send patient to scheduling?',
                'options'  => ['Yes', 'No'],
                // You can define on_true/on_false or leave as-is
                'on_true'  => 'Continue discharge workflow',
                'on_false' => 'No discharge needed',
            ],
            'group_can_write'   => ['discharge_coordinator','social_worker'],
            'group_can_see'     => ['discharge_coordinator','social_worker'],
            'group_get_notif'   => ['pt_ot','nurse','provider','social_worker','family'],
],
        ];

        foreach ($steps as $stepData) {
            $stepData['workflow_stage_id'] = $exclusionStage->id;
            WorkflowStep::create($stepData);
        }
    }
}
