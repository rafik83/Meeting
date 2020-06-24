<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\Admin\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class NewPasswordHandler
{
    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var PasswordEncoderInterface */
    private $encoder;

    /** @var SaltGeneratorInterface */
    private $saltGenerator;

    /** @var ForgottenPasswordTokenRepositoryInterface */
    private $forgottenPasswordToken;

    public function __construct(
        AdminRepositoryInterface $adminRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator,
        ForgottenPasswordTokenRepositoryInterface $forgottenPasswordToken
    ) {
        $this->adminRepository = $adminRepository;
        $this->encoder = $encoder;
        $this->saltGenerator = $saltGenerator;
        $this->forgottenPasswordToken = $forgottenPasswordToken;
    }

    public function handle(NewPassword $newPassword): void
    {
        $admin = $newPassword->admin;
        $salt = $this->saltGenerator->generate();
        $password = $this->encoder->encode($admin->updatePassword($salt, null), $newPassword->password);

        $admin->updatePassword($salt, $password);
        $this->adminRepository->set($admin);
        $this->forgottenPasswordToken->deleteAllForUser($admin);
    }
}
