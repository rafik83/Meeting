<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\EventListener;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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
     * LoginEventListener constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository, SheetRepositoryInterface $sheetRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * Set last login datetime on user sheets
     *
     * @param InteractiveLoginEvent $interactiveLoginEvent
     */
    public function onLogin(InteractiveLoginEvent $interactiveLoginEvent)
    {
        $user = $interactiveLoginEvent->getAuthenticationToken()->getUser();
        $host = $interactiveLoginEvent->getRequest()->getHost();

        $event = $this->eventRepository->getEventByDomain($host);

        if (!$user instanceof User) {
            return;
        }

        $sheets = $this->sheetRepository->getSheetByUserAndEvent($user, $event);

        foreach ($sheets as $sheet) {
            $this->sheetRepository->set($sheet->setLastLoginAt(new \DateTime()));
        }
    }
}
