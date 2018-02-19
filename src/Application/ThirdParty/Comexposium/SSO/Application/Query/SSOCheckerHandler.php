<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\ComboEmailUserNotValidException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\UserNotFoundException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\UserNotOnEventException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\TokenChecker;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class SSOCheckerHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TokenChecker */
    private $tokenChecker;

    /**
     * @param UserRepositoryInterface  $userRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param TokenChecker             $tokenChecker
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        TokenChecker $tokenChecker
    ) {
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->tokenChecker = $tokenChecker;
    }

    /**
     * @param SSOChecker $query
     *
     * @return User
     *
     * @throws UserNotFoundException
     * @throws UserNotOnEventException
     * @throws ComboEmailUserNotValidException
     */
    public function handle(SSOChecker $query): User
    {
        $user = $this->userRepository->findByEmail($query->email);

        // Check existence of User
        if ($user === null) {
            throw new UserNotFoundException(sprintf('User with mail %s not found', $query->email));
        }

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $query->event);

        // Check that User is on the event
        if (empty($sheets)) {
            throw new UserNotOnEventException(
                sprintf(
                    'User with mail %s not found on Event %d',
                    $query->email,
                    $query->event->getId()
                )
            );
        }

        // Check that combo email / token is valid
        $comboValid = $this->tokenChecker->isMailTokenComboValid($query->email, $query->token);

        if ($comboValid) {
            return $user;
        }

        throw new ComboEmailUserNotValidException(
            sprintf(
                'Combo email %s and token %s not valid',
                $query->email,
                $query->token
            )
        );
    }
}
