<?php

namespace Tests\Feature;

use App\Auth\TabAuthentication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class TabScopedAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_authentication_is_isolated_between_tabs(): void
    {
        $firstUser = User::factory()->create(['name' => 'First tab user']);
        $secondUser = User::factory()->create(['name' => 'Second tab user']);
        $authSessionKey = Auth::guard()->getName();
        $firstTabId = (string) Str::uuid();
        $secondTabId = (string) Str::uuid();

        $this->withSession([$authSessionKey => $firstUser->id])
            ->get('/?papirus_tab='.$firstTabId)
            ->assertOk()
            ->assertSee($firstTabId)
            ->assertDontSee($firstUser->name);

        Route::middleware('web')->get('/_test/tab-login/{tabId}/{user}', function (string $tabId, User $user) {
            app(TabAuthentication::class)->login($tabId, $user, false);
            Auth::guard('papirus-tab')->setUser($user);

            return response(Auth::user()->name);
        });

        $this->withHeaders(['X-Papirus-Tab' => $firstTabId])
            ->get('/_test/tab-login/'.$firstTabId.'/'.$secondUser->id)
            ->assertOk()
            ->assertSeeText($secondUser->name);

        Route::middleware('web')->post('/livewire-test/update', fn () => response(Auth::user()?->name ?? 'guest'));

        $this->withHeaders([
            'X-Livewire' => '1',
        ])
            ->post('/livewire-test/update', [
                'components' => [[
                    'snapshot' => json_encode(['data' => ['tabId' => $firstTabId]]),
                ]],
            ])
            ->assertOk()
            ->assertSeeText($secondUser->name);

        $this->withSession([$authSessionKey => $firstUser->id])
            ->get('/?papirus_tab='.$firstTabId)
            ->assertOk()
            ->assertSee($secondUser->name)
            ->assertDontSee($firstUser->name);

        $this->withHeaders(['X-Papirus-Tab' => $secondTabId])
            ->get('/')
            ->assertOk()
            ->assertDontSee($firstUser->name)
            ->assertDontSee($secondUser->name);

        $this->withHeaders(['X-Papirus-Tab' => $secondTabId])
            ->get('/_test/tab-login/'.$secondTabId.'/'.$firstUser->id)
            ->assertOk()
            ->assertSeeText($firstUser->name);

        Route::middleware('web')->get('/_test/tab-logout/{tabId}', function (string $tabId) {
            app(TabAuthentication::class)->logout($tabId);
            Auth::guard('papirus-tab')->forgetUser();

            return response('ok');
        });

        $this->withHeaders(['X-Papirus-Tab' => $firstTabId])
            ->get('/_test/tab-logout/'.$firstTabId)
            ->assertOk();

        $this->withSession([$authSessionKey => $firstUser->id])
            ->get('/?papirus_tab='.$firstTabId)
            ->assertOk()
            ->assertDontSee($firstUser->name)
            ->assertDontSee($secondUser->name);

        $this->withHeaders(['X-Papirus-Tab' => $secondTabId])
            ->get('/?papirus_tab='.$secondTabId)
            ->assertOk()
            ->assertSee($firstUser->name)
            ->assertDontSee($secondUser->name);

        $this->assertSame($firstUser->id, session($authSessionKey));
    }
}
