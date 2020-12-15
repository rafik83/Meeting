<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\AdminTemporarilyDisabledEvent;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class AdminAuthenticationFailureSubscriber implements EventSubscriberInterface
{
    /** @var RequestStack */
    private $requestStack;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        RequestStack $requestStack,
        AdminRepositoryInterface $adminRepository,
        DelayedEventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->requestStack = $requestStack;
        $this->adminRepository = $adminRepository;
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

        if (!$token instanceof UsernamePasswordToken || $token->getProviderKey() !== 'admin') {
            return;
        }

        $email = $token->getUser();
        $admin = $this->adminRepository->findByEmail($email);

        if (null === $admin) {
            return;
        }

        $admin->updateLastFailedAuthentication($this->dateTime);
        $this->adminRepository->set($admin);

        if (!$admin->isTemporarilyDisabledDueToFailedAuthentication($this->dateTime)) {
            return;
        }

        $request = $this->requestStack->getMasterRequest();

        if (null === $request) {
            return;
        }

        $this->eventDispatcher->dispatch(
            Events::ADMIN_ACCOUNT_TEMPORARILY_DISABLED,
            new AdminTemporarilyDisabledEvent($admin, $admin->getLocale())
        );
    }
}
