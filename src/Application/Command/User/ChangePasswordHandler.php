<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ChangePasswordHandler
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
     * @param ChangePassword $changePassword
     */
    public function handle(ChangePassword $changePassword)
    {
        $user     = $changePassword->user;
        $salt     = $this->saltGenerator->generate();
        $password = $this->encoder->encode($user->updatePassword($salt, null), $changePassword->plainPassword);
        $user->updatePassword($salt, $password);
        $this->userRepository->set($user);
    }
}
