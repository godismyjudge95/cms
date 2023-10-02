<?php

namespace Statamic\Policies;

use Statamic\Facades\GlobalSet;
use Statamic\Facades\User;

class GlobalSetPolicy
{
    use Concerns\HasMultisitePolicy;

    public function before($user, $ability, $set)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure globals')) {
            return true;
        }

        if ($this->siteIsForbidden($user, $set)) {
            return false;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return ! GlobalSet::all()->filter(function ($set) use ($user) {
            return $this->view($user, $set);
        })->isEmpty();
    }

    public function create($user)
    {
        // handled by before()
    }

    public function view($user, $set)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("edit {$set->handle()} globals");
    }

    public function edit($user, $set)
    {
        // handled by before()
    }

    public function configure($user, $set)
    {
        // handled by before()
    }

    public function delete($user, $set)
    {
        // handled by before()
    }
}
