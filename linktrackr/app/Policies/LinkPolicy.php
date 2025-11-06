<?php

namespace App\Policies;

use App\Models\Link;
use App\Models\User;

class LinkPolicy
{
    /**
     * Determine if the user can view the link
     */
    public function view(User $user, Link $link): bool
    {
        return $user->id === $link->user_id;
    }

    /**
     * Determine if the user can update the link
     */
    public function update(User $user, Link $link): bool
    {
        return $user->id === $link->user_id;
    }

    /**
     * Determine if the user can delete the link
     */
    public function delete(User $user, Link $link): bool
    {
        return $user->id === $link->user_id;
    }
}

// =============================================================================
// Don't forget to register the policy in:
// FILE: app/Providers/AuthServiceProvider.php
// =============================================================================

/*
protected $policies = [
    Link::class => LinkPolicy::class,
];
*/