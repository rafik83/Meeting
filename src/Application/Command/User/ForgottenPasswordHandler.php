<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Components\Token\ForgottenPasswordTokenGenerator;
use Proximum\Vimeet\Application\Event\ApplicationEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\ResetPasswordEvent;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Repository\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ForgottenPasswordHandler
{
    /**
     * @var ForgottenPasswordTokenGenerator
     */
    private $forgottenPasswordTokenGenerator;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ForgottenPasswordTokenRepositoryInterface
     */
    private $forgottenPasswordRepository;

    /**
     * @var ApplicationEventDispatcherInterface
     */
    private $applicationEventDispatcher;

    /**
     * @param ForgottenPasswordTokenGenerator           $forgottenPasswordTokenGenerator
     * @param UserRepositoryInterface                   $userRepository
     * @param ForgottenPasswordTokenRepositoryInterface $forgottenPasswordTokenRepository
     * @param ApplicationEventDispatcherInterface       $applicationEventDispatcher
     */
    public function __construct(
        ForgottenPasswordTokenGenerator $forgottenPasswordTokenGenerator,
        UserRepositoryInterface $userRepository,
        ForgottenPasswordTokenRepositoryInterface $forgottenPasswordTokenRepository,
        ApplicationEventDispatcherInterface $applicationEventDispatcher
    ) {
        $this->forgottenPasswordTokenGenerator = $forgottenPasswordTokenGenerator;
        $this->userRepository                  = $userRepository;
        $this->forgottenPasswordRepository     = $forgottenPasswordTokenRepository;
        $this->applicationEventDispatcher      = $applicationEventDispatcher;
    }

    /**
     * @param ForgottenPassword $forgottenPassword
     *
     * @throws EmailDoesNotExistException
     */
    public function handle(ForgottenPassword $forgottenPassword)
    {
        $user = $this->userRepository->findByEmail($forgottenPassword->email);

        if (null === $user) {
            throw new EmailDoesNotExistException();
        }

        $forgottenPasswordToken = $this->forgottenPasswordTokenGenerator->generate($user);

        $this->forgottenPasswordRepository->deleteAllForUser($user);
        $this->forgottenPasswordRepository->create($forgottenPasswordToken);

        $event = new ResetPasswordEvent(
            $user,
            $forgottenPassword->eventView,
            $forgottenPasswordToken,
            $forgottenPassword->locale
        );

        $this->applicationEventDispatcher->dispatch('reset_password', $event);
    }
}
