<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@stripeinvoicing.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        // Company Users
        $companyUsers = [
            [
                'name' => 'John Smith',
                'email' => 'john@techcorp.com',
                'password' => Hash::make('password'),
                'role' => 'company',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@marketingpro.com',
                'password' => Hash::make('password'),
                'role' => 'company',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael@consultgroup.com',
                'password' => Hash::make('password'),
                'role' => 'company',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'My Broker Cloud',
                'email' => 'support@mybrokercloud.com',
                'password' => Hash::make('password'),
                'role' => 'company',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($companyUsers as $userData) {
            User::create($userData);
        }

        // Agent Users (will be created by companies later in seeder chain)
        $agentUsers = [
            [
                'name' => 'Alice Cooper',
                'email' => 'alice@techcorp.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@techcorp.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Carol Davis',
                'email' => 'carol@marketingpro.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'David Miller',
                'email' => 'david@marketingpro.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Emma Garcia',
                'email' => 'emma@consultgroup.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Frank Martinez',
                'email' => 'frank@consultgroup.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Ashique Hassan',
                'email' => 'zihad@blubirdinteractive.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Richard Harless',
                'email' => 'Richard@mybrokercloud.com',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($agentUsers as $userData) {
            User::create($userData);
        }
    }
}
