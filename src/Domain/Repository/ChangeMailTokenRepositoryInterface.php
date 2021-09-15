<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\User;

interface ChangeMailTokenRepositoryInterface
{
    /**
     * @param ChangeMailToken $changeMailToken
     */
    public function create(ChangeMailToken $changeMailToken);

    /**
     * @param User $user
     */
    public function deleteAllForUser(User $user);
}
