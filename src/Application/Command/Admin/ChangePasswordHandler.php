<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class ChangePasswordHandler
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
     * @param AdminRepositoryInterface $adminRepository
     * @param PasswordEncoderInterface $encoder
     * @param SaltGeneratorInterface   $saltGenerator
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator
    ) {
        $this->adminRepository = $adminRepository;
        $this->encoder        = $encoder;
        $this->saltGenerator  = $saltGenerator;
    }

    /**
     * @param ChangePassword $changePassword
     */
    public function handle(ChangePassword $changePassword)
    {
        $admin    = $changePassword->admin;
        $salt     = $this->saltGenerator->generate();
        $password = $this->encoder->encode($admin->updatePassword($salt, null), $changePassword->plainPassword);
        $admin->updatePassword($salt, $password);
        $this->adminRepository->set($admin);
    }
}
