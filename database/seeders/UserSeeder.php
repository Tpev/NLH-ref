<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates/updates users with the same groups, each using
     * firstname.lastname.demo! style passwords (lowercase).
     *
     * Also creates a personal team for each user if not already present.
     *
     * @return void
     */
    public function run()
    {
        // All users share the same groups
        $commonGroups = ["nurse", "provider", "admin", "physician", "social_worker", "ma", "pt_ot"];

        // Manually assign passwords in the desired "firstname.lastname.demo!" format (all lowercase).
        $usersData = [
            // 1) Peverelli T.
            [
                'email'    => 'peverelli.t@gmail.com',
                'name'     => 'Peverelli T.',
                'password' => Hash::make('testtest'),
                'group'    => $commonGroups,
            ],

            [
                'email'    => 'charlespp42@gmail.com',
                'name'     => 'Charles Petrini Poli',
                // folded the middle name into the last name portion
                'password' => Hash::make('charles.petrini.poli.demo!'),
                'group'    => $commonGroups,
            ],

            [
                'email'    => 'davidsone@northernlight.org',
                'name'     => 'Eva Davidson',
                'password' => Hash::make('Eva.Davidson.demo!'),
                'group'    => $commonGroups,
            ],
  
            [
                'email'    => 'lunta@northernlight.org',
                'name'     => 'Amanda Lunt',
                'password' => Hash::make('Amanda.Lunt.demo!'),
                'group'    => $commonGroups,
            ],

            [
                'email'    => 'e.brondolo@northeastern.edu',
                'name'     => 'E Brondolo',
                'password' => Hash::make('e.brondolo.demo!'),
                'group'    => $commonGroups,
            ],
          
            [
                'email'    => 'stuarts@northernlight.org',
                'name'     => 'Shelly Stuart',
                'password' => Hash::make('Shelly.Stuart.demo!'),
                'group'    => $commonGroups,
            ],
           
        ];

        foreach ($usersData as $userData) {
            // Create or update each user by email
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name'     => $userData['name'],
                    'password' => $userData['password'],
                    'group'    => $userData['group'],
                ]
            );

            $this->command->info('UserSeeder: User with email ' . $userData['email'] . ' created/updated.');

            // Create personal team if it doesn't exist
            if (!$user->ownedTeams()->where('personal_team', true)->exists()) {
                $firstName = explode(' ', $user->name, 2)[0];
                $team = $user->ownedTeams()->create([
                    'name'          => "{$firstName}'s Team",
                    'personal_team' => true,
                ]);

                $this->command->info("UserSeeder: Personal team '{$team->name}' created for '{$user->email}'.");
            } else {
                $this->command->info("UserSeeder: Personal team already exists for '{$user->email}'.");
            }
        }
    }
}
