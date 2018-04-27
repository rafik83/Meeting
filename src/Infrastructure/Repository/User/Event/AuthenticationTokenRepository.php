<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\User\Event;

use Proximum\Vimeet\Domain\Model\User\Event\AuthenticationToken;
use Proximum\Vimeet\Domain\Repository\User\Event\AuthenticationTokenRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Repository\AbstractDoctrineRepository;

class AuthenticationTokenRepository extends AbstractDoctrineRepository implements AuthenticationTokenRepositoryInterface
{
    public function add(AuthenticationToken $authenticationToken): void
    {
        $this->entityManager->persist($authenticationToken);
        $this->entityManager->flush($authenticationToken);
    }
}
