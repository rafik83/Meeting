<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
