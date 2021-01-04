<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Components\Token\AdminForgottenPasswordTokenGenerator;
use Proximum\Vimeet\Application\Event\Admin\ResetPasswordEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Repository\Admin\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ForgottenPasswordHandler
{
    /**
     * @var AdminForgottenPasswordTokenGenerator
     */
    private $forgottenPasswordTokenGenerator;

    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var ForgottenPasswordTokenRepositoryInterface
     */
    private $forgottenPasswordRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param AdminForgottenPasswordTokenGenerator      $forgottenPasswordTokenGenerator
     * @param AdminRepositoryInterface                  $adminRepository
     * @param ForgottenPasswordTokenRepositoryInterface $forgottenPasswordTokenRepository
     * @param EventDispatcherInterface                  $eventDispatcher
     */
    public function __construct(
        AdminForgottenPasswordTokenGenerator $forgottenPasswordTokenGenerator,
        AdminRepositoryInterface $adminRepository,
        ForgottenPasswordTokenRepositoryInterface $forgottenPasswordTokenRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->forgottenPasswordTokenGenerator = $forgottenPasswordTokenGenerator;
        $this->adminRepository                 = $adminRepository;
        $this->forgottenPasswordRepository     = $forgottenPasswordTokenRepository;
        $this->eventDispatcher                 = $eventDispatcher;
    }

    /**
     * @param ForgottenPassword $forgottenPassword
     *
     * @throws EmailDoesNotExistException
     */
    public function handle(ForgottenPassword $forgottenPassword)
    {
        $admin = $this->adminRepository->findByEmail($forgottenPassword->email);

        if (null === $admin) {
            throw new EmailDoesNotExistException();
        }

        $forgottenPasswordToken = $this->forgottenPasswordTokenGenerator->generate($admin);

        $this->forgottenPasswordRepository->deleteAllForUser($admin);
        $this->forgottenPasswordRepository->create($forgottenPasswordToken);

        $event = new ResetPasswordEvent(
            $admin,
            $forgottenPasswordToken,
            $forgottenPassword->locale
        );

        $this->eventDispatcher->dispatch(Events::ADMIN_PASSWORD_RESET, $event);
    }
}
