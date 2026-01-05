<?php

namespace App\Observers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    public function updating(User $user): void
    {
        if (! $user->isDirty('role')) {
            return;
        }

        $actor = Auth::user();

        if (! $actor) {
            return;
        }

        if (! $actor->isAdmin()) {
            abort(403, 'You cannot change role.');
        }
        if ($actor->isAdmin() && $user->role == UserRole::CUSTOMER->value) {
            abort(403, 'You cannot change role.');
        }
    }
}
