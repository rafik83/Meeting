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
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class RegisterHandler
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
     * @param UserRepositoryInterface  $userRepository
     * @param PasswordEncoderInterface $encoder
     * @param SaltGeneratorInterface   $saltGenerator
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator
    ) {
        $this->userRepository = $userRepository;
        $this->encoder        = $encoder;
        $this->saltGenerator  = $saltGenerator;
    }

    /**
     * @param Register $register
     *
     * @throws EmailAlreadyExistsException
     */
    public function handle(Register $register)
    {
        if ($this->userRepository->emailExists($register->email)) {
            throw new EmailAlreadyExistsException(sprintf('"%s" already exists.', $register->email));
        }

        $salt     = $this->saltGenerator->generate();
        $user     = new User($register->email, $salt, null, $register->locale);
        $password = $this->encoder->encode($user, $register->password);
        $user->updatePassword($salt, $password);

        $this->userRepository->add($user);

        $register->user = $user;
    }
}
