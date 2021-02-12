<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomainValueResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserConnectedEventListener implements EventSubscriberInterface
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var NetworkingAccessChecker */
    private $networkingAccessChecker;

    /** @var UserDomainValueResolver */
    private $userResolver;

    /** @var SessionInterface */
    private $session;

    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    /** @var DatetimeInterface */
    private $datetime;

    /** @var Sheet */
    private $sheet;

    /** @var UserDomain */
    private $userDomain;

    /** @var bool */
    private $publishNotification = false;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        NetworkingAccessChecker $networkingAccessChecker,
        UserDomainValueResolver $userResolver,
        SessionInterface $session,
        NotificationPublisherInterface $notificationPublisher,
        DateTimeInterface $datetime
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->networkingAccessChecker = $networkingAccessChecker;
        $this->userResolver = $userResolver;
        $this->session = $session;
        $this->notificationPublisher = $notificationPublisher;
        $this->datetime = $datetime;
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

        $this->userDomain = $this->userResolver->getUserDomain();
        if (null === $this->userDomain) {
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
        if ($lastNotification < ($this->datetime->getTimestamp() - 600)) {
            $this->publishNotification = true;
        }

        $this->session->set('connectedLastSeen', $this->datetime->getTimestamp());
    }

    public function onTerminate(KernelEvent $event): void
    {
        if ($this->publishNotification) {
            $this->notificationPublisher->publishUserConnectionNotification($this->sheet, $this->userDomain->getUser());
        }
    }
}
