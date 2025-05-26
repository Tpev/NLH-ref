<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowStage;

class WorkflowStageSeeder extends Seeder
{
    public function run()
    {
        // Retrieve the created workflow
        $workflow = Workflow::where('name', 'Referral Management Demo Workflow')->firstOrFail();

        // Stage 1: Triage and backlog resolution
        $stage1 = WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name'        => 'Initial Triage & Backlog Check',
            'order'       => 1,
        ]);

        // Stage 2: Follow-up & sedation check
        $stage2 = WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name'        => 'Follow-up & Sedation Assessment',
            'order'       => 2,
        ]);

        // Stage 3: Final Steps & Scheduling
        $stage3 = WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name'        => 'Finalize & Schedule',
            'order'       => 3,
        ]);
    }
}
