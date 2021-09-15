<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Proximum\Vimeet\Application\Adapter\SaltGeneratorInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\ResetPasswordConfirmEvent;
use Proximum\Vimeet\Domain\Repository\User\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NewPasswordHandler
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
     * @var ForgottenPasswordTokenRepositoryInterface
     */
    private $forgottenPasswordToken;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param UserRepositoryInterface                   $userRepository
     * @param PasswordEncoderInterface                  $encoder
     * @param SaltGeneratorInterface                    $saltGenerator
     * @param ForgottenPasswordTokenRepositoryInterface $forgottenPasswordToken
     * @param EventDispatcherInterface                  $eventDispatcher
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordEncoderInterface $encoder,
        SaltGeneratorInterface $saltGenerator,
        ForgottenPasswordTokenRepositoryInterface $forgottenPasswordToken,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->userRepository         = $userRepository;
        $this->encoder                = $encoder;
        $this->saltGenerator          = $saltGenerator;
        $this->forgottenPasswordToken = $forgottenPasswordToken;
        $this->eventDispatcher        = $eventDispatcher;
    }

    /**
     * @param NewPassword $newPassword
     */
    public function handle(NewPassword $newPassword)
    {
        $user     = $newPassword->user;
        $salt     = $this->saltGenerator->generate();
        $password = $this->encoder->encode($user->updatePassword($salt, null), $newPassword->password);

        $user->updatePassword($salt, $password);
        $this->userRepository->set($user);
        $this->forgottenPasswordToken->deleteAllForUser($user);

        // trigger password reset confirmation event
        $event = new ResetPasswordConfirmEvent($user, $newPassword->event, $user->getLocale());
        $this->eventDispatcher->dispatch(Events::USER_RESET_PASSWORD_CONFIRMED, $event);
    }
}
