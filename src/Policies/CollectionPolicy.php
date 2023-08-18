<?php

namespace Statamic\Policies;

use Statamic\Facades\Collection;
use Statamic\Facades\User;

class CollectionPolicy
{
    use HasMultisitePolicy;

    public function before($user, $ability, ...$arguments)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure collections')) {
            return true;
        }

        if (! $this->accessInSelectedSite($user, $arguments)) {
            return false;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return ! Collection::all()->filter(function ($collection) use ($user) {
            return $this->view($user, $collection);
        })->isEmpty();
    }

    public function create($user)
    {
        // handled by before()
    }

    public function store($user)
    {
        // handled by before()
    }

    public function view($user, $collection)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("view {$collection->handle()} entries");
    }

    public function edit($user, $collection)
    {
        // handled by before()
    }

    public function update($user, $collection)
    {
        // handled by before()
    }

    public function delete($user, $collection)
    {
        // handled by before()
    }

    public function reorder($user, $collection)
    {
        $user = User::fromUser($user);

        return $collection->hasStructure() && $user->hasPermission("reorder {$collection->handle()} entries");
    }
}
