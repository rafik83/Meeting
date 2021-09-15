<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class ChangePasswordHandler
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var PasswordEncoderInterface */
    private $encoder;

    /** @var SaltGeneratorInterface */
    private $saltGenerator;

    public function __construct(
        AdminRepositoryInterface $adminRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator
    ) {
        $this->adminRepository = $adminRepository;
        $this->encoder = $encoder;
        $this->saltGenerator  = $saltGenerator;
    }

    public function handle(ChangePassword $changePassword): void
    {
        $admin = $changePassword->admin;
        $salt = $this->saltGenerator->generate();
        $password = $this->encoder->encode($admin->updatePassword($salt, null), $changePassword->plainPassword);
        $admin->updatePassword($salt, $password);
        $this->adminRepository->set($admin);
    }
}
