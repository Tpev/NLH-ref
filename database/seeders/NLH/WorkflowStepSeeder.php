<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\WorkflowStep;

class WorkflowStepSeeder extends Seeder
{
    public function run()
    {
        // Retrieve the relevant workflow & stages
        $workflow = Workflow::where('name', 'Referral Management Demo Workflow')->firstOrFail();
        $stage1   = WorkflowStage::where('workflow_id', $workflow->id)->where('order', 1)->firstOrFail();
        $stage2   = WorkflowStage::where('workflow_id', $workflow->id)->where('order', 2)->firstOrFail();
        $stage3   = WorkflowStage::where('workflow_id', $workflow->id)->where('order', 3)->firstOrFail();

        /*
         |=================================================================
         | Stage 1: "Initial Triage & Backlog Check"
         |=================================================================
        */

        // Step 1: Gather Basic Referral Info (Form)
        WorkflowStep::create([
            'workflow_stage_id' => $stage1->id,
            'name'              => 'Gather Basic Referral Info',
            'type'              => 'form',
            'order'             => 1,
            'metadata'          => [
                'fields' => [
                    [
                        'name'     => 'patient_first_name',
                        'type'     => 'text',
                        'label'    => 'First Name',
                        'required' => true,
                    ],
                    [
                        'name'     => 'patient_last_name',
                        'type'     => 'text',
                        'label'    => 'Last Name',
                        'required' => true,
                    ],
                    [
                        'name'     => 'reason_for_referral',
                        'type'     => 'textarea',
                        'label'    => 'Reason for referral / Clinical notes',
                        'required' => false,
                    ],
                ],
            ],
        ]);

        // Step 2: Decision step: backlog or immediate triage
        WorkflowStep::create([
            'workflow_stage_id' => $stage1->id,
            'name'              => 'Backlog Priority',
            'type'              => 'decision',
            'order'             => 2,
            'metadata'          => [
                'question' => 'Is this referral backlogged or can it be triaged immediately?',
                'options'  => ['Backlogged', 'Immediate'],
                'on_true'  => 'Proceed to thorough backlog process',
                'on_false' => 'Proceed to Stage 2',
            ],
        ]);

        // Step 3: Checkbox step confirming older referrals are reviewed
        WorkflowStep::create([
            'workflow_stage_id' => $stage1->id,
            'name'              => 'Confirm older referrals reviewed',
            'type'              => 'checkbox',
            'order'             => 3,
            'metadata'          => [
                'label' => 'Mark once older referrals in backlog have been addressed',
            ],
        ]);

        /*
         |=================================================================
         | Stage 2: "Follow-up & Sedation Assessment"
         |=================================================================
        */

        // Step 4: Decision step – sedation or not
        WorkflowStep::create([
            'workflow_stage_id' => $stage2->id,
            'name'              => 'Sedation Required?',
            'type'              => 'decision',
            'order'             => 1,
            'metadata'          => [
                'question' => 'Does the patient require sedation or anesthesia review?',
                'options'  => ['Yes', 'No'],
                'on_true'  => 'Upload sedation docs',
                'on_false' => 'Proceed with scheduling',
            ],
        ]);

        // Step 5: Upload step for sedation docs
        WorkflowStep::create([
            'workflow_stage_id' => $stage2->id,
            'name'              => 'Upload Sedation Documents',
            'type'              => 'upload',
            'order'             => 2,
            'metadata'          => [
                'upload_label'   => 'Attach sedation or anesthesia clearance docs',
                'allowed_mimes'  => ['pdf', 'jpg', 'png'],
                'max_files'      => 3,
                'max_size'       => 2048,
            ],
        ]);

        // Step 6: Checkbox step to confirm follow-up calls
        WorkflowStep::create([
            'workflow_stage_id' => $stage2->id,
            'name'              => 'Confirm Follow-up Calls',
            'type'              => 'checkbox',
            'order'             => 3,
            'metadata'          => [
                'label' => 'Have you attempted follow-up calls for older referrals?',
            ],
        ]);

        /*
         |=================================================================
         | Stage 3: "Finalize & Schedule"
         |=================================================================
        */

        // Step 7: Another form step to finalize scheduling details
        WorkflowStep::create([
            'workflow_stage_id' => $stage3->id,
            'name'              => 'Schedule Procedure',
            'type'              => 'form',
            'order'             => 1,
            'metadata'          => [
                'fields' => [
                    [
                        'name'     => 'proposed_date',
                        'type'     => 'date',
                        'label'    => 'Proposed Procedure Date',
                        'required' => true,
                    ],
                    [
                        'name'     => 'location',
                        'type'     => 'text',
                        'label'    => 'Preferred Location',
                        'required' => true,
                    ],
                    [
                        'name'     => 'additional_notes',
                        'type'     => 'textarea',
                        'label'    => 'Additional Scheduling Notes',
                        'required' => false,
                    ],
                ],
            ],
        ]);

        // Step 8: Final decision step
        WorkflowStep::create([
            'workflow_stage_id' => $stage3->id,
            'name'              => 'Final Confirmation',
            'type'              => 'decision',
            'order'             => 2,
            'metadata'          => [
                'question' => 'Are all referral requirements satisfied?',
                'options'  => ['Yes, schedule', 'No, request more info'],
                'on_true'  => 'Referral is ready to schedule',
                'on_false' => 'Collect additional documents',
            ],
        ]);
    }
}
