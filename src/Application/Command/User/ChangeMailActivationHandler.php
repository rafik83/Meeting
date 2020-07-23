<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\UserEmailChangeActivatedEvent;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Repository\ChangeMailTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ChangeMailActivationHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ChangeMailTokenRepositoryInterface */
    private $changeMailTokenRepository;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ChangeMailTokenRepositoryInterface $changeMailTokenRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->userRepository = $userRepository;
        $this->changeMailTokenRepository = $changeMailTokenRepository;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    public function handle(ChangeMailActivation $changeMailActivation): void
    {
        $mail = StringHelper::trimSpacesAndNonBreakSpaces($changeMailActivation->mail);
        $user = $changeMailActivation->user;

        $user->updateEmail($mail);
        $this->userRepository->set($user);

        $this->changeMailTokenRepository->deleteAllForUser($user);

        $this->delayedEventDispatcher->dispatch(
            Events::USER_EMAIL_CHANGE_ACTIVATED,
            new UserEmailChangeActivatedEvent(
                $user
            )
        );
    }
}
