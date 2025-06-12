<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            WorkflowSeeder::class,
			UserSeeder::class,
            WorkflowStageSeeder::class,
            WorkflowStepSeeder::class,
            ReferralProgressSeeder::class,
            ReferralSeeder::class,
        ]);
    }
}
