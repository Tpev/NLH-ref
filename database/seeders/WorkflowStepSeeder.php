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
    /* ───────────────────── APPENDIX A ───────────────────── */

    // 1. CNS / Neurologic
    ['name' => 'cns_dementia_modsev',      'type' => 'checkbox', 'label' => 'CNS – Moderate / severe dementia'],
    ['name' => 'cns_stroke_90d',           'type' => 'checkbox', 'label' => 'CNS – Stroke / TIA within 3 months'],
    ['name' => 'cns_other_disorders',      'type' => 'checkbox', 'label' => 'CNS – ALS / MS / Parkinson’s with functional limits'],

    // 2. Cardiovascular
    ['name' => 'cardiac_stent_des_180d',   'type' => 'checkbox', 'label' => 'Cardiac – Drug-eluting stent < 6 months'],
    ['name' => 'cardiac_stent_bms_30d',    'type' => 'checkbox', 'label' => 'Cardiac – Bare-metal stent < 30 days'],
    ['name' => 'cardiac_angina_unstable',  'type' => 'checkbox', 'label' => 'Cardiac – Unstable angina'],
    ['name' => 'cardiac_valve_severe_hcm', 'type' => 'checkbox', 'label' => 'Cardiac – Severe hypertrophic cardiomyopathy'],
    ['name' => 'cardiac_phtn_severe',      'type' => 'checkbox', 'label' => 'Cardiac – Severe pulmonary hypertension'],
    ['name' => 'cardiac_chf_ef_lt30',      'type' => 'checkbox', 'label' => 'Cardiac – CHF, EF < 30 %'],

    // 3. Pulmonary
    ['name' => 'pulm_o2_dependent',        'type' => 'checkbox', 'label' => 'Pulmonary – Oxygen-dependent (not night-only)'],
    ['name' => 'pulm_sats_lt92',           'type' => 'checkbox', 'label' => 'Pulmonary – Resting SpO₂ < 92 %'],
    ['name' => 'pulm_asthma_exac_30d',     'type' => 'checkbox', 'label' => 'Pulmonary – Asthma exacerbation < 30 days'],
    ['name' => 'pulm_copd_exac_30d',       'type' => 'checkbox', 'label' => 'Pulmonary – COPD exacerbation < 30 days'],
    ['name' => 'pulm_sleep_apnea_noncompliant','type'=>'checkbox','label'=>'Pulmonary – CPAP/BiPAP non-compliance'],
    ['name' => 'pulm_tracheostomy',        'type' => 'checkbox', 'label' => 'Pulmonary – Tracheostomy present'],

    // 4. Endocrine
    ['name' => 'endocrine_a1c_gt85',       'type' => 'checkbox', 'label' => 'Endocrine – Hgb A1c > 8.5 %'],

    // 5. Pregnancy
    ['name' => 'pregnancy',                'type' => 'checkbox', 'label' => 'Pregnancy – Currently pregnant'],

    // 6. Renal
    ['name' => 'renal_dialysis',           'type' => 'checkbox', 'label' => 'Renal – Dialysis-dependent'],

    // 7. Liver
    ['name' => 'liver_esld',               'type' => 'checkbox', 'label' => 'Liver – End-stage cirrhosis (ESLD)'],

    // 8. Coagulation
    ['name' => 'coag_platelets_lt75k',     'type' => 'checkbox', 'label' => 'Coag – Platelets < 75 K'],
    ['name' => 'coag_cannot_stop_anticoag','type' => 'checkbox', 'label' => 'Coag – Cannot stop anticoagulation'],
    ['name' => 'coag_disorder',            'type' => 'checkbox', 'label' => 'Coag – von Willebrand / Factor deficiency / Hemophilia'],
    ['name' => 'coag_dvt_pe_current',      'type' => 'checkbox', 'label' => 'Coag – Current DVT / PE on anticoag'],

    // 9. Anesthesia
    ['name' => 'anesthesia_delirium_history','type'=>'checkbox','label'=>'Anesthesia – History of post-op delirium'],
    ['name' => 'anesthesia_mh_history',    'type' => 'checkbox', 'label' => 'Anesthesia – Malignant hyperthermia (self / family)'],
    ['name' => 'anesthesia_difficult_airway','type'=>'checkbox','label'=>'Anesthesia – Known difficult airway'],
    ['name' => 'anesthesia_msk_limit_neck','type' => 'checkbox', 'label' => 'Anesthesia – Limited neck ROM (RA, AS, etc.)'],

    // 10. Pediatrics
    ['name' => 'peds_age_lt12m',           'type' => 'checkbox', 'label' => 'Pediatrics – Age < 12 months'],
    ['name' => 'peds_tonsillectomy_lt3y',  'type' => 'checkbox', 'label' => 'Pediatrics – Tonsillectomy patient < 3 yrs'],
    ['name' => 'peds_mh_family',           'type' => 'checkbox', 'label' => 'Pediatrics – Family history malignant hyperthermia'],
    ['name' => 'peds_congenital_comorbid', 'type' => 'checkbox', 'label' => 'Pediatrics – Congenital / complex comorbidities'],

    // 11. BMI
    ['name' => 'bmi_gt50',                 'type' => 'checkbox', 'label' => 'BMI – > 50'],
    ['name' => 'bmi_gt45_abd',             'type' => 'checkbox', 'label' => 'BMI – > 45 (intra-abdominal case)'],
    ['name' => 'bmi_40_50_eval',           'type' => 'checkbox', 'label' => 'BMI – 40-50 (needs anesthesia review)'],

    // 12. Opioids
    ['name' => 'opioid_chronic_suboxone',  'type' => 'checkbox', 'label' => 'Opioids – Suboxone / chronic opioid use'],

    // 13. Mobility
    ['name' => 'mobility_nonambulatory_hoyer','type'=>'checkbox','label'=>'Mobility – Non-ambulatory / needs Hoyer lift'],

    /* ───────────────────── APPENDIX B (Outpatient Total-Joint) ───────────────────── */

    ['name' => 'joint_bmi_gt40',           'type' => 'checkbox', 'label' => 'T-Joint – BMI > 40'],
    ['name' => 'joint_no_home_support',    'type' => 'checkbox', 'label' => 'T-Joint – No home support for 7 days'],
    ['name' => 'joint_not_ambulating',     'type' => 'checkbox', 'label' => 'T-Joint – Not ambulating with cane pre-op'],
    ['name' => 'joint_mets_lt4',           'type' => 'checkbox', 'label' => 'T-Joint – METs < 4'],
    ['name' => 'joint_resp_card_compromise','type'=>'checkbox','label'=>'T-Joint – Respiratory / cardiac compromise'],
    ['name' => 'joint_asa_iii_or_more',    'type' => 'checkbox', 'label' => 'T-Joint – ASA III + (needs approval)'],
    ['name' => 'joint_untreated_osa',      'type' => 'checkbox', 'label' => 'T-Joint – Untreated obstructive sleep apnea'],
    ['name' => 'joint_chronic_opioids',    'type' => 'checkbox', 'label' => 'T-Joint – Chronic opioids / Suboxone'],
    ['name' => 'joint_age_ge75',           'type' => 'checkbox', 'label' => 'T-Joint – Age ≥ 75 yrs'],
    ['name' => 'joint_a1c_gt75',           'type' => 'checkbox', 'label' => 'T-Joint – Hgb A1c > 7.5 %'],
    ['name' => 'joint_urinary_retention_history','type'=>'checkbox','label'=>'T-Joint – History of urinary retention'],
],

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
