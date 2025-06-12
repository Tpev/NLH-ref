<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Referral;
use App\Models\ReferralProgress;
use App\Models\User;
use App\Models\Workflow;
use Carbon\Carbon;

class ReferralSeeder extends Seeder
{
    public function run(): void
    {
        $workflow = Workflow::first(); // Assumes at least one workflow exists
        $user = User::first();         // Assumes at least one user exists

        if (!$workflow || !$user) {
            $this->command->error('No workflow or user found. Seed those first.');
            return;
        }

        foreach (range(1, 100) as $i) {
            $first = fake()->firstName;
            $last = fake()->lastName;
            $dob = fake()->date('Y-m-d', '-65 years');
            $procedure = fake()->randomElement([
                'EGD',
                'Colonoscopy',
                'Colonoscopy with Polypectomy'
            ]);

            $formData = [
                'first_name' => $first,
                'last_name' => $last,
                'dob' => $dob,
                'primary_phone' => fake()->phoneNumber,
                'referring_physician' => fake()->name,
                'referring_facility' => fake()->company,
                'referring_phone' => fake()->phoneNumber,
                'referral_type' => 'procedure',
                'reason' => fake()->sentence,
                'diagnosis' => fake()->word,
                'gi_procedures' => [$procedure],
                'clinical_summary' => fake()->paragraph,
            ];

            // Create referral with backdated creation
            $createdAt = Carbon::now('UTC')->subDays(rand(5, 30))->startOfDay();


            $referral = Referral::create([
                'workflow_id' => $workflow->id,
                'form_data' => json_encode($formData),
                'status' => 'submitted',
                'notes' => json_encode([]),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Simulate "Submitted to Anesthesia"
            if ($i % 2 === 0) {
                $progressTime = $createdAt->copy()->addDays(rand(1, 4))->setTime(rand(8, 16), rand(0, 59));
                ReferralProgress::create([
                    'referral_id' => $referral->id,
                    'workflow_step_id' => 2,
                    'completed_by' => $user->id,
                    'completed_at' => $progressTime,
                    'status' => 'completed',
                    'notes' => 'Yes',
                    'created_at' => $progressTime,
                    'updated_at' => $progressTime,
                ]);
            }

            // Simulate "Sent to Scheduling"
            if ($i % 3 === 0) {
                $progressTime = $createdAt->copy()->addDays(rand(3, 10))->setTime(rand(8, 16), rand(0, 59));
                ReferralProgress::create([
                    'referral_id' => $referral->id,
                    'workflow_step_id' => 3,
                    'completed_by' => $user->id,
                    'completed_at' => $progressTime,
                    'status' => 'completed',
                    'notes' => 'Yes',
                    'created_at' => $progressTime,
                    'updated_at' => $progressTime,
                ]);
            }
        }
    }
}
