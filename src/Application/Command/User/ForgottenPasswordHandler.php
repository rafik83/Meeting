<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Components\Token\UserForgottenPasswordTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\ResetPasswordEvent;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Repository\User\ForgottenPasswordTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ForgottenPasswordHandler
{
    /**
     * @var UserForgottenPasswordTokenGenerator
     */
    private $forgottenPasswordTokenGenerator;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ForgottenPasswordTokenRepositoryInterface
     */
    private $forgottenPasswordRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param UserForgottenPasswordTokenGenerator       $forgottenPasswordTokenGenerator
     * @param UserRepositoryInterface                   $userRepository
     * @param ForgottenPasswordTokenRepositoryInterface $forgottenPasswordTokenRepository
     * @param EventDispatcherInterface                  $eventDispatcher
     */
    public function __construct(
        UserForgottenPasswordTokenGenerator $forgottenPasswordTokenGenerator,
        UserRepositoryInterface $userRepository,
        ForgottenPasswordTokenRepositoryInterface $forgottenPasswordTokenRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->forgottenPasswordTokenGenerator = $forgottenPasswordTokenGenerator;
        $this->userRepository                  = $userRepository;
        $this->forgottenPasswordRepository     = $forgottenPasswordTokenRepository;
        $this->eventDispatcher                 = $eventDispatcher;
    }

    /**
     * @param ForgottenPassword $forgottenPassword
     *
     * @return ForgottenPasswordToken
     * @throws EmailDoesNotExistException
     */
    public function handle(ForgottenPassword $forgottenPassword): ForgottenPasswordToken
    {
        $user = $this->userRepository->findByEmail($forgottenPassword->email);

        if (null === $user) {
            throw new EmailDoesNotExistException();
        }

        $forgottenPasswordToken = $this->forgottenPasswordTokenGenerator->generate($user);

        $this->forgottenPasswordRepository->deleteAllForUser($user);
        $this->forgottenPasswordRepository->create($forgottenPasswordToken);

        $event = new ResetPasswordEvent(
            $user,
            $forgottenPassword->event,
            $forgottenPasswordToken,
            $forgottenPassword->locale,
            $forgottenPassword->requestedByAdmin
        );

        $this->eventDispatcher->dispatch(Events::USER_PASSWORD_RESET, $event);

        return $forgottenPasswordToken;
    }
}
