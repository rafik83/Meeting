<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Command\Admin\UpdateLastLogin;
use Proximum\Vimeet\Application\Command\Admin\UpdateLastLoginHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class LoginEventListener
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UpdateLastLoginHandler
     */
    private $updateLastLoginHandler;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * LoginEventListener constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param RequestStack             $requestStack
     * @param UpdateLastLoginHandler   $updateLastLoginHandler
     * @param EntityManager            $entityManager
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        SheetRepositoryInterface $sheetRepository,
        RequestStack $requestStack,
        UpdateLastLoginHandler $updateLastLoginHandler,
        EntityManager $entityManager
    ) {
        $this->eventRepository        = $eventRepository;
        $this->sheetRepository        = $sheetRepository;
        $this->requestStack           = $requestStack;
        $this->updateLastLoginHandler = $updateLastLoginHandler;
        $this->entityManager          = $entityManager;
    }

    /**
     * Set last login datetime on user sheets
     *
     * @param AuthenticationEvent $authenticationEvent
     */
    public function onLoginSuccess(AuthenticationEvent $authenticationEvent)
    {
        $user = $authenticationEvent->getAuthenticationToken()->getUser();
        $host = $this->requestStack->getMasterRequest()->getHost();

        $event = $this->eventRepository->getEventByDomain($host);

        if (!$user instanceof User) {

            if ($user instanceof Admin) {
                $this->entityManager->detach($user);

                $this->updateLastLoginHandler->handle(
                    new UpdateLastLogin(
                        $user->getEmail(),
                        new \DateTime()
                    )
                );
            }

            return;
        }

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        foreach ($sheets as $sheet) {
            $this->sheetRepository->set($sheet->setLastLoginAt(new \DateTime()));
        }
    }
}
