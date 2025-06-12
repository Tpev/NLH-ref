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



    // 11. BMI
    ['name' => 'bmi_gt50',                 'type' => 'checkbox', 'label' => 'BMI – > 50'],
    ['name' => 'bmi_gt45_abd',             'type' => 'checkbox', 'label' => 'BMI – > 45 (intra-abdominal case)'],
    ['name' => 'bmi_40_50_eval',           'type' => 'checkbox', 'label' => 'BMI – 40-50 (needs anesthesia review)'],

    // 12. Opioids
    ['name' => 'opioid_chronic_suboxone',  'type' => 'checkbox', 'label' => 'Opioids – Suboxone / chronic opioid use'],

    // 13. Mobility
    ['name' => 'mobility_nonambulatory_hoyer','type'=>'checkbox','label'=>'Mobility – Non-ambulatory / needs Hoyer lift'],


/* ───────────────  Propofol / Anesthesia-Review criteria (new)  ─────────────── */

    // Age / BMI
    ['name' => 'prop_age_ge80',            'type' => 'checkbox', 'label' => 'Propofol – Age ≥ 80 yr (no comorbidity req)'],
    ['name' => 'prop_age_ge80_comorb',     'type' => 'checkbox', 'label' => 'Propofol – Age ≥ 80 yr with comorbidities'],
    ['name' => 'prop_bmi_lt20',            'type' => 'checkbox', 'label' => 'Propofol – BMI < 20'],
    ['name' => 'prop_bmi_gt35',            'type' => 'checkbox', 'label' => 'Propofol – BMI > 35'],
    ['name' => 'prop_bmi_45_55',           'type' => 'checkbox', 'label' => 'Propofol – BMI 45-55 (EGD/colono: OR)'],

    // Respiratory
    ['name' => 'prop_difficult_intubation','type' => 'checkbox', 'label' => 'Propofol – Prior difficult airway / intubation'],
    ['name' => 'prop_severe_copd',         'type' => 'checkbox', 'label' => 'Propofol – Severe COPD'],
    ['name' => 'prop_pulm_htn',            'type' => 'checkbox', 'label' => 'Propofol – Pulmonary hypertension'],
    ['name' => 'prop_sleep_apnea',         'type' => 'checkbox', 'label' => 'Propofol – Sleep apnea (± CPAP)'],
    ['name' => 'prop_steroid_lung',        'type' => 'checkbox', 'label' => 'Propofol – On oral steroids for lung disease'],
    ['name' => 'prop_home_oxygen',         'type' => 'checkbox', 'label' => 'Propofol – Home oxygen therapy'],

    // Cardiac / vascular
    ['name' => 'prop_recent_cardio_event','type' => 'checkbox', 'label' => 'Propofol – Cardiac / CV / TE event < 1 yr'],
    ['name' => 'prop_des_gt6m',            'type' => 'checkbox', 'label' => 'Propofol – DES (only) > 6 months'],
    ['name' => 'prop_aortic_stenosis',     'type' => 'checkbox', 'label' => 'Propofol – Mod/sev aortic stenosis'],
    ['name' => 'prop_angina_unstable',     'type' => 'checkbox', 'label' => 'Propofol – Unstable / worsening angina'],
    ['name' => 'prop_ef_le45',             'type' => 'checkbox', 'label' => 'Propofol – LV EF ≤ 45 %'],
    ['name' => 'prop_cardiomyopathy',      'type' => 'checkbox', 'label' => 'Propofol – Cardiomyopathy'],

    // Renal / GI / other medical
    ['name' => 'prop_ckd_stage4',          'type' => 'checkbox', 'label' => 'Propofol – CKD stage IV'],
    ['name' => 'prop_esophagectomy',       'type' => 'checkbox', 'label' => 'Propofol – Post-esophagectomy'],
    ['name' => 'prop_esophageal_varices',  'type' => 'checkbox', 'label' => 'Propofol – Esophageal varices'],
    ['name' => 'prop_tortuous_colon',      'type' => 'checkbox', 'label' => 'Propofol – Prior difficult colon / Crohn’s flare'],
    ['name' => 'prop_dementia',            'type' => 'checkbox', 'label' => 'Propofol – Dementia (any)'],

    // Social / environment
    ['name' => 'prop_congregate_setting',  'type' => 'checkbox', 'label' => 'Propofol – Lives in congregate setting'],

    // Medications & substance use
    ['name' => 'prop_narcotic_use',        'type' => 'checkbox', 'label' => 'Propofol – Active narcotic use'],
    ['name' => 'prop_benzo_use',           'type' => 'checkbox', 'label' => 'Propofol – Benzodiazepine use'],
    ['name' => 'prop_sleep_aids',          'type' => 'checkbox', 'label' => 'Propofol – Sleep-aid use < 40 yr'],
    ['name' => 'prop_methadone_suboxone',  'type' => 'checkbox', 'label' => 'Propofol – Methadone / Suboxone / Naltrexone'],
    ['name' => 'prop_sedation_resistance', 'type' => 'checkbox', 'label' => 'Propofol – Prior high-dose sedation needed'],
    ['name' => 'prop_opioid_use_disorder', 'type' => 'checkbox', 'label' => 'Propofol – Opioid use disorder'],
    ['name' => 'prop_alcohol_use_disorder','type' => 'checkbox', 'label' => 'Propofol – Active alcohol use disorder (< 1 yr)'],
    ['name' => 'prop_daily_marijuana',     'type' => 'checkbox', 'label' => 'Propofol – Daily marijuana use'],

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
