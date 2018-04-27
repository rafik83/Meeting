<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UserManager
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param string|null $email
     *
     * @return User
     */
    public function create($email = null)
    {
        if (null === $email) {
            $email = sprintf('%s@example.net', uniqid());
        }

        $user  = UserFactory::create($email);

        $this->userRepository->add($user);

        return $user;
    }
}
