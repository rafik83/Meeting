<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Components\Template\Exception\MissingRequiredDataException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataValidator;

class ParticipateHandler
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
     * @var TemplateDataValidator
     */
    private $templateDataValidator;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param ParticipantRepositoryInterface $participantRepository
     * @param TemplateDataValidator          $templateDataValidator
     * @param \DateTimeInterface             $dateTime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        TemplateDataValidator $templateDataValidator,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->participantRepository = $participantRepository;
        $this->templateDataValidator = $templateDataValidator;
        $this->dateTime              = $dateTime;
    }

    /**
     * @param Participate $participate
     *
     * @throws MissingRequiredDataException
     */
    public function handle(Participate $participate)
    {
        // Create a new sheet for this event
        $sheet = new Sheet($participate->event, $participate->type, [], [], $this->dateTime);

        // Check the constraint on the data (required)
        $this
            ->templateDataValidator
            ->validateFirstParticipantDataFromType($participate->type, $participate->locale, $participate->data);

        $this->sheetRepository->add($sheet);

        // Create a new participant
        $participant = new Participant($sheet, $participate->user, $participate->data, $participate->owner, true);
        $this->participantRepository->add($participant);

        $participate->sheet       = $sheet;
        $participate->participant = $participant;
    }
}
