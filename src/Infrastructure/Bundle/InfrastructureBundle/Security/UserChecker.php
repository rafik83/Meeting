<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Exception\SheetDisabledException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserChecker as SymfonyUserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker extends SymfonyUserChecker
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * UserChecker constructor.
     *
     * @param RequestStack             $requestStack
     * @param EventRepositoryInterface $eventRepository
     * @param SheetRepositoryInterface $sheetRepository
     *
     */
    public function __construct(
        RequestStack $requestStack,
        EventRepositoryInterface $eventRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->sheetRepository = $sheetRepository;
        $this->requestStack    = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        parent::checkPostAuth($user);

        $event = $this->eventRepository->getEventByDomain($this->requestStack->getCurrentRequest()->getHost());

        if (null === $event || !$user instanceof User) {
            return;
        }

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        if (!empty($sheets)) {
            /** @var Sheet $sheet */
            $sheet = reset($sheets);

            if (false === $sheet->isEnabled()) {
                throw new SheetDisabledException('login.error.sheetDisabled');
            }
        }
    }
}
