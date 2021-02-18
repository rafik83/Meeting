<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
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
    private NotificationPublisherInterface $notificationPublisher;
    private DatetimeInterface $dateTime;
    private ?Sheet $sheet;
    private ?UserDomain $currentUser;
    private bool $publishNotification = false;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        UserDomainProvider $userDomainProvider,
        NetworkingAccessChecker $networkingAccessChecker,
        SessionInterface $session,
        NotificationPublisherInterface $notificationPublisher,
        DateTimeInterface $dateTime
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->userDomainProvider = $userDomainProvider;
        $this->networkingAccessChecker = $networkingAccessChecker;
        $this->session = $session;
        $this->notificationPublisher = $notificationPublisher;
        $this->dateTime = $dateTime;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onController',
            KernelEvents::TERMINATE => 'onTerminate',
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

        if (!$this->networkingAccessChecker->allowedToAccess($this->sheet->getEvent())) {
            return;
        }

        // publish notification after 10 min of inactivity, to reduce number of notifications
        $lastNotification = $this->session->get('connectedLastSeen');
        if ($lastNotification < ($this->dateTime->getTimestamp() - 600)) {
            $this->publishNotification = true;
        }

        $this->session->set('connectedLastSeen', $this->dateTime->getTimestamp());
    }

    public function onTerminate(KernelEvent $event): void
    {
        if ($this->publishNotification) {
            $this->notificationPublisher->publishUserConnectionNotification($this->sheet, $this->currentUser->getUser());
        }
    }
}
