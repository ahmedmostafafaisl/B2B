<?php

namespace App\Http\Resources\User\Concerns;

trait HasPresence
{
    /**
     * is_online is true when the user's last login was within the last
     * 5 minutes. last_seen_at is simply last_login_at, ISO-formatted.
     */
    protected function presence(): array
    {
        $lastLogin = $this->last_login_at;

        return [
            'is_online' => $lastLogin !== null && $lastLogin->gt(now()->subMinutes(5)),
            'last_seen_at' => optional($lastLogin)->toISOString(),
        ];
    }
}
