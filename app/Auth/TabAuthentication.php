<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use UnexpectedValueException;

class TabAuthentication
{
    private const CACHE_PREFIX = 'papirus.auth_tab.';

    public static function tabIdFromRequest(Request $request): ?string
    {
        $tabId = $request->header('X-Papirus-Tab');

        if (
            $tabId === null
            && (
                ($request->isMethod('GET') && $request->is('/'))
                || $request->header('X-Livewire') === '1'
            )
        ) {
            $tabId = $request->query('papirus_tab');
        }

        if ($tabId === null && $request->header('X-Livewire') === '1') {
            $components = $request->input('components', []);

            abort_unless(is_array($components), 400);

            foreach ($components as $component) {
                $snapshot = is_array($component) ? ($component['snapshot'] ?? null) : null;

                if (! is_string($snapshot)) {
                    continue;
                }

                $decodedSnapshot = json_decode($snapshot, true);

                if (! is_array($decodedSnapshot)) {
                    continue;
                }

                $tabId = $decodedSnapshot['data']['tabId'] ?? null;

                if ($tabId !== null) {
                    break;
                }
            }
        }

        if ($tabId === null) {
            return null;
        }

        abort_unless(is_string($tabId) && Str::isUuid($tabId), 400);

        return $tabId;
    }

    public function userForRequest(Request $request): ?User
    {
        $tabId = self::tabIdFromRequest($request);

        return $tabId === null ? null : $this->userFor($tabId);
    }

    public function userFor(string $tabId): ?User
    {
        $identity = Cache::get($this->cacheKey($tabId));

        if ($identity === null) {
            return null;
        }

        if (
            ! is_array($identity)
            || (! is_int($identity['user_id'] ?? null) && ! is_string($identity['user_id'] ?? null))
            || ! is_bool($identity['remember'] ?? null)
        ) {
            throw new UnexpectedValueException('Invalid cached Papirus tab authentication data.');
        }

        $user = User::query()->find($identity['user_id']);

        if ($user === null) {
            $this->logout($tabId);

            return null;
        }

        Cache::put($this->cacheKey($tabId), $identity, $this->expiration($identity['remember']));

        return $user;
    }

    public function login(string $tabId, User $user, bool $remember): void
    {
        Cache::put($this->cacheKey($tabId), [
            'user_id' => $user->getKey(),
            'remember' => $remember,
        ], $this->expiration($remember));
    }

    public function logout(string $tabId): void
    {
        Cache::forget($this->cacheKey($tabId));
    }

    private function cacheKey(string $tabId): string
    {
        return self::CACHE_PREFIX.hash('sha256', $tabId);
    }

    private function expiration(bool $remember): \DateTimeInterface
    {
        return $remember
            ? now()->addDays(30)
            : now()->addMinutes(config('session.lifetime'));
    }
}
