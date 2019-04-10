<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\User\Import;

use Proximum\Vimeet\Domain\Model\User;

class ImportResult
{
    /** @var User[] */
    private $addedUsers = [];

    /** @var User[] */
    private $updatedUsers = [];

    /**
     * @return User[]
     */
    public function getAddedUsers(): array
    {
        return $this->addedUsers;
    }

    public function countAddedUsers(): int
    {
        return count($this->addedUsers);
    }

    /**
     * @return User[]
     */
    public function getUpdatedUsers(): array
    {
        return $this->updatedUsers;
    }

    public function countUpdatedUsers(): int
    {
        return count($this->updatedUsers);
    }

    public function addedUser(User $user)
    {
        $this->addedUsers[$user->getId()] = $user;
    }

    public function updatedUser(User $user)
    {
        $this->updatedUsers[$user->getId()] = $user;
    }
}
