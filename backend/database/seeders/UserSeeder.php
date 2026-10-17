<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private const USERS = [
        ['Admin', 'admin@periplast.ro', Role::Admin],
        ['Programmer', 'programmer@periplast.ro', Role::Programmer],
        ['Operator', 'operator@periplast.ro', Role::Operator],
        ['Quality Manager', 'qm@periplast.ro', Role::QualityManager],
    ];

    public function run(): void
    {
        foreach (self::USERS as [$name, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'periplast', 'role' => $role]
            );
        }
    }
}
