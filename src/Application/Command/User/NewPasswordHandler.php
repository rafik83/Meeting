<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class NewPasswordHandler
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var PasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var SaltGeneratorInterface
     */
    private $saltGenerator;

    /**
     * @var ForgottenPasswordTokenRepositoryInterface
     */
    private $forgottenPasswordToken;

    /**
     * @param UserRepositoryInterface                   $userRepository
     * @param PasswordEncoderInterface                  $encoder
     * @param SaltGeneratorInterface                    $saltGenerator
     * @param ForgottenPasswordTokenRepositoryInterface $forgottenPasswordToken
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator,
        ForgottenPasswordTokenRepositoryInterface $forgottenPasswordToken
    ) {
        $this->userRepository         = $userRepository;
        $this->encoder                = $encoder;
        $this->saltGenerator          = $saltGenerator;
        $this->forgottenPasswordToken = $forgottenPasswordToken;
    }

    /**
     * @param NewPassword $newPassword
     */
    public function handle(NewPassword $newPassword)
    {
        $user     = $newPassword->user;
        $salt     = $this->saltGenerator->generate();
        $password = $this->encoder->encode($user->updatePassword($salt, null), $newPassword->password);

        $user->updatePassword($salt, $password);
        $this->userRepository->set($user);
        $this->forgottenPasswordToken->deleteAllForUser($user);
    }
}
