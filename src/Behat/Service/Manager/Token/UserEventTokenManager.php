<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager\Token;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;

class UserEventTokenManager
{
    /** @var UserEventTokenRepositoryInterface */
    private $userEventTokenRepository;

    /**
     * @param UserEventTokenRepositoryInterface $userEventTokenRepository
     */
    public function __construct(UserEventTokenRepositoryInterface $userEventTokenRepository)
    {
        $this->userEventTokenRepository = $userEventTokenRepository;
    }

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $type
     *
     * @return UserEventToken
     */
    public function create(Event $event, User $user, $type)
    {
        $token = new UserEventToken($event, $user, $type, 'token', new \DateTime());

        $this->userEventTokenRepository->add($token);

        return $token;
    }
}
