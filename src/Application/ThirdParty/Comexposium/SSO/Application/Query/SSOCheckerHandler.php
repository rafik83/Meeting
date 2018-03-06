<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\EmailToUserConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\ComboEmailUserNotValidException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\UserNotFoundException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\UserNotOnEventException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\TokenChecker;
use Proximum\Vimeet\Domain\Model\Event;
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

    /** @var EmailToUserConverter */
    private $emailToUserConverter;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        TokenChecker $tokenChecker,
        EmailToUserConverter $emailToUserConverter
    ) {
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->tokenChecker = $tokenChecker;
        $this->emailToUserConverter = $emailToUserConverter;
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
        if (!$user instanceof User) {
            if ($query->isExhibitor) {
                throw new UserNotFoundException(sprintf('User with mail %s not found', $query->email));
            }

            return $this->handleNotKnownVisitorLogin($query->event, $query->email, $query->token, $query->locale);
        }

        return $this->handleKnownUserLogin($query->event, $user, $query->token, $query->isExhibitor);
    }

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $token
     * @param bool   $isExhibitor
     *
     * @return User
     * @throws ComboEmailUserNotValidException
     * @throws UserNotOnEventException
     */
    private function handleKnownUserLogin(Event $event, User $user, string $token, bool $isExhibitor): User
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        if (empty($sheets) && $isExhibitor) {
            throw new UserNotOnEventException(
                sprintf(
                    'User with mail %s not found on Event %d',
                    $user->getEmail(),
                    $event->getId()
                )
            );
        }

        $this->checkEmailAndToken($user->getEmail(), $token);

        return $user;
    }

    /**
     * @param Event  $event
     * @param string $email
     * @param string $token
     *
     * @param string $locale
     *
     * @return User
     * @throws ComboEmailUserNotValidException
     */
    private function handleNotKnownVisitorLogin(Event $event, string $email, string $token, string $locale): User
    {
        $this->checkEmailAndToken($email, $token);

        $user = $this->emailToUserConverter->handle($event, $email, $locale);

        if (!$user instanceof User) {
            throw new \LogicException('Do a custom error please');
        }

        return $user;
    }

    /**
     * @param string $email
     * @param string $token
     *
     * @throws ComboEmailUserNotValidException
     */
    private function checkEmailAndToken(string $email, string $token): void
    {
        if (!$this->tokenChecker->isMailTokenComboValid($email, $token)) {
            throw new ComboEmailUserNotValidException(
                sprintf(
                    'Combo email %s and token %s not valid',
                    $email,
                    $token
                )
            );
        }
    }
}
