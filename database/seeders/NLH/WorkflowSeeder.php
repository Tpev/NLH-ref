<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;

class WorkflowSeeder extends Seeder
{
    public function run()
    {
        // Create a single workflow that addresses backlogs, follow-ups, and complex criteria
        Workflow::create([
            'name' => 'Referral Management Demo Workflow',
            'description' => 'Demonstrates handling backlogs, follow-ups, and sedation complexities in referrals.',
        ]);
    }
}
