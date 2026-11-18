<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            ['Repair', 'repair@periplast.ro', Role::Reparation],
            ['Maintenance', 'maintenance@periplast.ro', Role::Maintenance],
        ] as [$name, $email, $role]) {
            User::query()->firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'periplast', 'role' => $role],
            );
        }
    }

    public function down(): void
    {
        User::query()->whereIn('email', ['repair@periplast.ro', 'maintenance@periplast.ro'])->delete();
    }
};
