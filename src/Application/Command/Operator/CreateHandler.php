<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Operator;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class CreateHandler
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
        $this->encoder         = $encoder;
        $this->saltGenerator   = $saltGenerator;
    }

    public function handle(Create $create)
    {
        if ($this->adminRepository->emailExists($create->email)) {
            throw new EmailAlreadyExistsException(sprintf('"%s" already exists.', $create->email));
        }

        $salt = $this->saltGenerator->generate();

        $admin = new Admin(
            $create->email,
            $salt,
            null,
            $create->organizer->getLocale(),
            $create->firstname,
            $create->lastname,
            Admin::ROLE_OPERATOR
        );

        $password = $this->encoder->encode($admin, $create->password);
        $admin->updatePassword($salt, $password);

        foreach ($create->organizer->getEvents() as $event) {
            $admin->addEvent($event);
        }

        $this->adminRepository->add($admin);
    }
}
