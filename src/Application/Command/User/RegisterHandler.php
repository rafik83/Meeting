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
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class RegisterHandler
{
    private $userRepository;

    private $encoder;

    public function __construct(UserRepositoryInterface $userRepository, PasswordEncoderInterface $encoder)
    {
        $this->userRepository = $userRepository;
        $this->encoder        = $encoder;
    }

    public function handle(Register $register)
    {
        if ($this->userRepository->emailExists($register->email)) {
            throw new EmailAlreadyExistsException(sprintf('"%s" already exists.', $register->email));
        }

        $salt     = uniqid();
        $password = $this->encoder->encode($register->password, $salt);

        $user = new User($register->email, $salt, $password);

        $this->userRepository->add($user);
    }
}
