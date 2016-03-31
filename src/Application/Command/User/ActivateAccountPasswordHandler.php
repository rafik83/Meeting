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
use Proximum\Vimeet\Domain\Repository\User\ActivateAccountTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ActivateAccountPasswordHandler
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
     * @var ActivateAccountTokenRepositoryInterface
     */
    private $activateAccountTokenRepository;

    /**
     * @param UserRepositoryInterface                 $userRepository
     * @param PasswordEncoderInterface                $encoder
     * @param SaltGeneratorInterface                  $saltGenerator
     * @param ActivateAccountTokenRepositoryInterface $activateAccountTokenRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator,
        ActivateAccountTokenRepositoryInterface $activateAccountTokenRepository
    ) {
        $this->userRepository                 = $userRepository;
        $this->encoder                        = $encoder;
        $this->saltGenerator                  = $saltGenerator;
        $this->activateAccountTokenRepository = $activateAccountTokenRepository;
    }

    /**
     * @param ActivateAccountPassword $activateAccountPassword
     */
    public function handle(ActivateAccountPassword $activateAccountPassword)
    {
        $user     = $activateAccountPassword->user;
        $salt     = $this->saltGenerator->generate();
        $password = $this->encoder->encode($user->updatePassword($salt, null), $activateAccountPassword->password);

        $user->updatePassword($salt, $password);
        $this->userRepository->set($user);
        $this->activateAccountTokenRepository->deleteAllForUser($user);
    }
}
