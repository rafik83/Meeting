<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Bundle\InfrastructureBundle\Repository\Admin\ActivateAccountTokenRepository;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class ActivateAccountPasswordHandler
{
    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var PasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var SaltGeneratorInterface
     */
    private $saltGenerator;

    /**
     * @var ActivateAccountTokenRepository
     */
    private $activateAccountTokenRepository;

    /**
     * @param AdminRepositoryInterface       $adminRepository
     * @param PasswordEncoderInterface       $encoder
     * @param SaltGeneratorInterface         $saltGenerator
     * @param ActivateAccountTokenRepository $activateAccountTokenRepository
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator,
        ActivateAccountTokenRepository $activateAccountTokenRepository
    ) {
        $this->adminRepository                = $adminRepository;
        $this->encoder                        = $encoder;
        $this->saltGenerator                  = $saltGenerator;
        $this->activateAccountTokenRepository = $activateAccountTokenRepository;
    }

    /**
     * @param ActivateAccountPassword $activateAccountPassword
     */
    public function handle(ActivateAccountPassword $activateAccountPassword)
    {
        $admin    = $activateAccountPassword->admin;
        $salt     = $this->saltGenerator->generate();
        $password = $this->encoder->encode($admin->updatePassword($salt, null), $activateAccountPassword->password);

        $admin->updatePassword($salt, $password);
        $this->adminRepository->set($admin);
        $this->activateAccountTokenRepository->deleteAllForUser($admin);
    }
}
