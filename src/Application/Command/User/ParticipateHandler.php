<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Command\BaseHandler;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class ParticipateHandler extends BaseHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Participate $participate
     * @throws RequiredDataEmptyException
     */
    public function handle(Participate $participate)
    {
        // Check the constraint on the data (required)
        $this->checkDataConstraint($participate->data, $participate->type->getParticipantTemplate());

        // Create a new sheet for this event
        $sheet = new Sheet($participate->event, $participate->type, [], []);
        $this->sheetRepository->add($sheet);

        // Create a new participant
        $participant = new Participant($sheet, $participate->user, $participate->data, $participate->owner);
        $this->participantRepository->add($participant);

        $participate->sheet = $sheet;
    }
}
