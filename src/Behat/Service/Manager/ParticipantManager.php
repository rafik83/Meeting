<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\UserEventView\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;

class ParticipantManager
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var SheetManager */
    private $sheetManager;

    /** @var UserManager */
    private $userManager;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        SheetManager $sheetManager,
        UserManager $userManager,
        CommandBusInterface $commandBus
    ) {
        $this->participantRepository = $participantRepository;
        $this->sheetManager = $sheetManager;
        $this->userManager = $userManager;
        $this->commandBus = $commandBus;
    }

    /**
     * @param Event      $event
     * @param Sheet|null $sheet
     * @param User|null  $user
     *
     * @return Participant
     */
    public function create(Event $event, Sheet $sheet = null, User $user = null)
    {
        if (null === $sheet) {
            $sheet = $this->sheetManager->create($event);
        }

        if (null === $user) {
            $user = $this->userManager->create();
        }

        $participant = ParticipantFactory::create($sheet, $user);
        $participant->setData([]);
        $this->participantRepository->add($participant);
        $this->commandBus->handle(new Update($participant->getUser(), $participant->getEvent()));

        return $participant;
    }
}
