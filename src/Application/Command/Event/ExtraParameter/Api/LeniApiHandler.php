<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameter\Api;

use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Query\Api\Leni\LeniUserViewQuery;
use Proximum\Vimeet\Application\Query\Api\Leni\LeniUserViewQueryHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class LeniApiHandler
{
    /** @var ParticipantPlanningFormatter */
    private $participantPlanningFormatter;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var LeniUserViewQueryHandler */
    private $leniUserViewQueryHandler;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param EventRepositoryInterface          $eventRepository
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param UserRepositoryInterface           $userRepository
     * @param SheetRepositoryInterface          $sheetRepository
     * @param ParticipantPlanningFormatter      $participantPlanningFormatter
     * @param LeniUserViewQueryHandler          $leniUserViewQueryHandler
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        LeniUserViewQueryHandler $leniUserViewQueryHandler
    ) {
        $this->eventRepository = $eventRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->leniUserViewQueryHandler = $leniUserViewQueryHandler;
    }

    /**
     * @param LeniApi $command
     */
    public function handle(LeniApi $command): void
    {
        $events = $this->eventRepository->findEventWithLeniApiParameters();

        foreach ($events as $event) {
            $leniUser  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
            $leniEvent = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);

            $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($event);
            $users = $this->userRepository->findByEvent($event);

            $leniUserViews = [];

            foreach ($users as $user) {
                $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);
                $leniUserViews[] = $this->leniUserViewQueryHandler->handle(new LeniUserViewQuery($event, $user, $sheets));
            }
        }
    }
}
