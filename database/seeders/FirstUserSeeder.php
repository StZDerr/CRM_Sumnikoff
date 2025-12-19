<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FirstUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update initial user StZD and set as admin
        $user = User::firstOrNew(['login' => 'StZD']);
        $user->name = 'StZD';
        $user->email = $user->email; // keep existing email if present
        $user->email_verified_at = $user->email_verified_at ?? now();
        $user->password = $user->password ?? Hash::make('10031999Sasha');
        $user->role = 'admin';
        $user->save();
    }
}
