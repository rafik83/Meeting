<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\ConnectedEvent;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\UserDomainProvider;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserConnectedEventListener implements EventSubscriberInterface
{
    private AuthorizationCheckerAdapterInterface $authorizationChecker;
    private UserDomainProvider $userDomainProvider;
    private NetworkingAccessChecker $networkingAccessChecker;
    private SessionInterface $session;
    private DelayedEventDispatcherInterface $delayedEventDispatcher;
    private DatetimeInterface $dateTime;
    private ?Sheet $sheet;
    private ?UserDomain $currentUser;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        UserDomainProvider $userDomainProvider,
        NetworkingAccessChecker $networkingAccessChecker,
        SessionInterface $session,
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        DateTimeInterface $dateTime
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->userDomainProvider = $userDomainProvider;
        $this->networkingAccessChecker = $networkingAccessChecker;
        $this->session = $session;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->dateTime = $dateTime;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onController',
        ];
    }

    public function onController(KernelEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->currentUser = $this->userDomainProvider->getUserDomain();
        if (null === $this->currentUser) {
            $this->session->remove('connectedLastSeen');
            return;
        }

        if (!$this->authorizationChecker->isGranted('ROLE_USER')) {
            $this->session->remove('connectedLastSeen');
            return;
        }

        $this->sheet = $event->getRequest()->attributes->get('sheet');
        if (null === $this->sheet) {
            return;
        }

        if (!$this->networkingAccessChecker->isSheetAllowedToAccess($this->sheet)) {
            return;
        }

        // publish notification after 10 min of inactivity, to reduce number of notifications
        $lastNotification = $this->session->get('connectedLastSeen');
        if ($lastNotification < ($this->dateTime->getTimestamp() - 600)) {
            $this->delayedEventDispatcher->dispatch(Events::USER_CONNECTED, new ConnectedEvent($this->sheet, $this->currentUser->getUser()));
        }

        $this->session->set('connectedLastSeen', $this->dateTime->getTimestamp());
    }
}
