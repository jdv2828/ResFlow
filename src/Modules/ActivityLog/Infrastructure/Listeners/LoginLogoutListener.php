<?php

namespace Src\Modules\ActivityLog\Infrastructure\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LoginLogoutListener
{
    public function handle(object $event): void
    {
        if (!$event instanceof Login && !$event instanceof Logout) {
            return;
        }

        $action = $event instanceof Login ? 'user.login' : 'user.logout';

        ActivityLog::create([
            'user_id' => $event->user?->id,
            'action' => $action,
            'subject_type' => $event->user ? $event->user->getMorphClass() : null,
            'subject_id' => $event->user?->id,
            'data' => [
                'email' => $event->user?->email,
                'name' => $event->user?->name,
            ],
        ]);
    }
}
