<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_coordinator_with_coordinator_permissions(): void
    {
        (new UserSeeder)->run();

        $user = User::where('email', 'coordinator@periplast.ro')->firstOrFail();

        $this->assertSame('Coordinator', $user->name);
        $this->assertSame(Role::Coordinator, $user->role);
        $this->assertTrue(Hash::check('periplast', $user->password));
        $this->assertSame([
            Role::ASSIGN_ORDER,
            Role::START_MACHINE,
            Role::STOP_MACHINE,
            Role::REPORT_EQUIPMENT_WARNING,
            Role::CHECK_QUALITY,
        ], $user->role->permissions());
    }

    public function test_it_seeds_the_four_named_operators(): void
    {
        (new UserSeeder)->run();

        $operators = [
            'fancsali@periplast.ro' => 'Fancsali Arpad',
            'balazs@periplast.ro' => 'Balazs Csaba',
            'vajna@periplast.ro' => 'Vajna Karoly',
            'derzsi@periplast.ro' => 'Derzsi Karoly',
        ];

        foreach ($operators as $email => $name) {
            $user = User::where('email', $email)->firstOrFail();

            $this->assertSame($name, $user->name);
            $this->assertSame(Role::Operator, $user->role);
            $this->assertTrue(Hash::check('periplast', $user->password));
        }
    }
}
