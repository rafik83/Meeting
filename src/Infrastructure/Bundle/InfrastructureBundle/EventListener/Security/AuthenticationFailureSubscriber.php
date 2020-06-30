<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\UserTemporarilyDisabledEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class AuthenticationFailureSubscriber implements EventSubscriberInterface
{
    /** @var RequestStack */
    private $requestStack;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        RequestStack $requestStack,
        EventRepositoryInterface $eventRepository,
        UserRepositoryInterface $userRepository,
        DelayedEventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->requestStack = $requestStack;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime = $dateTime;
    }

    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'processException',
        ];
    }

    public function processException(AuthenticationFailureEvent $authenticationFailureEvent): void
    {
        $token = $authenticationFailureEvent->getAuthenticationToken();

        if (!$token instanceof UsernamePasswordToken || $token->getProviderKey() !== 'main') {
            return;
        }

        $email = $token->getUser();
        $user = $this->userRepository->findByEmail($email);

        if (null === $user) {
            return;
        }

        $user->updateLastFailedAuthentication($this->dateTime);
        $this->userRepository->set($user);

        if (!$user->isTemporarilyDisabledDueToFailedAuthentication($this->dateTime)) {
            return;
        }

        $request = $this->requestStack->getMasterRequest();

        if (null === $request) {
            return;
        }

        $event = $this->eventRepository->getEventByDomain($request->getHost());

        if (!$event instanceof Event) {
            return;
        }

        $this->eventDispatcher->dispatch(
            Events::USER_ACCOUNT_TEMPORARILY_DISABLED,
            new UserTemporarilyDisabledEvent($event, $user)
        );
    }
}
