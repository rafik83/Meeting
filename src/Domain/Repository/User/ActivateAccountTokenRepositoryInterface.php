<?php

namespace Proximum\Vimeet\Domain\Repository\User;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;

interface ActivateAccountTokenRepositoryInterface
{
    /**
     * @param ActivateAccountToken $activateAccountToken
     */
    public function create(ActivateAccountToken $activateAccountToken);

    /**
     * @param User $user
     */
    public function deleteAllForUser(User $user);
}
