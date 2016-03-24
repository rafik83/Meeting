<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\EventListener;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
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
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * LoginEventListener constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param RequestStack             $requestStack
     * @param AdminRepositoryInterface $adminRepository
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        SheetRepositoryInterface $sheetRepository,
        RequestStack $requestStack,
        AdminRepositoryInterface $adminRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->sheetRepository = $sheetRepository;
        $this->requestStack    = $requestStack;
        $this->adminRepository = $adminRepository;
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
                $user->setLastLoginAt(new \DateTime());
                $this->adminRepository->set($user);
            }
            return;
        }

        $sheets = $this->sheetRepository->getSheetByUserAndEvent($user, $event);

        foreach ($sheets as $sheet) {
            $this->sheetRepository->set($sheet->setLastLoginAt(new \DateTime()));
        }
    }
}
