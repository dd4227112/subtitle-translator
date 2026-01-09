<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('Admin@123456'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        
        // Assign admin role
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }
        
        // Create regular user
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('User@123456'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        
        // Assign user role
        if (!$user->hasRole('user')) {
            $user->assignRole($userRole);
        }
        
        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Default Credentials:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('Admin:');
        $this->command->info('  Email: admin@example.com');
        $this->command->info('  Password: Admin@123456');
        $this->command->info('');
        $this->command->info('Regular User:');
        $this->command->info('  Email: user@example.com');
        $this->command->info('  Password: User@123456');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
