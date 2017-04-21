<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Group;

use Proximum\Vimeet\Application\Exception\Group\UserNotAllowedToManageGroupException;
use Proximum\Vimeet\Application\Exception\Group\UserNotFoundForGivenEmailException;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;
use Proximum\Vimeet\Domain\View\Group\UserView;

class UserViewQueryHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var UserToGroupManagerChecker */
    private $userToGroupManagerChecker;

    /**
     * UserViewQueryHandler constructor.
     *
     * @param UserRepositoryInterface   $userRepository
     * @param UserToGroupManagerChecker $userToGroupManagerChecker
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        UserToGroupManagerChecker $userToGroupManagerChecker
    ) {
        $this->userRepository            = $userRepository;
        $this->userToGroupManagerChecker = $userToGroupManagerChecker;
    }

    /**
     * @param UserViewQuery $query
     *
     * @return UserView
     *
     * @throws UserNotAllowedToManageGroupException
     * @throws UserNotFoundForGivenEmailException
     */
    public function handle(UserViewQuery $query)
    {
        $user = $this->userRepository->findByEmail($query->email);

        if (!$user) {
            throw new UserNotFoundForGivenEmailException($query->email);
        }

        if (false === $this->userToGroupManagerChecker->isUserToGroupManagerAllowed($query->event, $user)) {
            throw new UserNotAllowedToManageGroupException($user->getFullname());
        }

        return new UserView($user->getId(), $user->getEmail(), $user->getFullname());
    }
}
