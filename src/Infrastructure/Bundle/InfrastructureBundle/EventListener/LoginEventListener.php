<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Command\Admin\UpdateLastLogin as AdminUpdateLastLogin;
use Proximum\Vimeet\Application\Command\Admin\UpdateLastLoginHandler as AdminUpdateLastLoginHandler;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLogin as SheetUpdateLastLogin;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLoginHandler as SheetUpdateLastLoginHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class LoginEventListener
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var AdminUpdateLastLoginHandler
     */
    private $adminUpdateLastLoginHandler;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /** @var SheetUpdateLastLoginHandler */
    private $sheetUpdateLastLoginHandler;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        RequestStack $requestStack,
        AdminUpdateLastLoginHandler $adminUpdateLastLoginHandler,
        EntityManager $entityManager,
        SheetUpdateLastLoginHandler $sheetUpdateLastLoginHandler
    ) {
        $this->eventRepository = $eventRepository;
        $this->requestStack = $requestStack;
        $this->adminUpdateLastLoginHandler = $adminUpdateLastLoginHandler;
        $this->entityManager = $entityManager;
        $this->sheetUpdateLastLoginHandler = $sheetUpdateLastLoginHandler;
    }

    /**
     * Set last login datetime on user sheets
     *
     * @param AuthenticationEvent $authenticationEvent
     */
    public function onLoginSuccess(AuthenticationEvent $authenticationEvent): void
    {
        $user = $authenticationEvent->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            if ($user instanceof Admin) {
                $this->entityManager->detach($user);

                $this->adminUpdateLastLoginHandler->handle(
                    new AdminUpdateLastLogin($user->getEmail())
                );
            }

            return;
        }

        $host = $this->requestStack->getMasterRequest()->getHost();
        $event = $this->eventRepository->getEventByDomain($host);

        if (!$event instanceof Event) {
            return;
        }

        $this->sheetUpdateLastLoginHandler->handle(new SheetUpdateLastLogin($event, $user));
    }
}
