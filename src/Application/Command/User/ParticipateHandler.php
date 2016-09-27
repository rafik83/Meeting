<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\UserEvent\TypeResolver;

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
     * @var Synchronizer
     */
    private $accountSynchronizer;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param ParticipantRepositoryInterface $participantRepository
     * @param TypeResolver                   $typeResolver
     * @param Synchronizer                   $accountSynchronizer
     * @param \DateTimeInterface             $dateTime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        TypeResolver $typeResolver,
        Synchronizer $accountSynchronizer,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->participantRepository = $participantRepository;
        $this->accountSynchronizer   = $accountSynchronizer;
        $this->dateTime              = $dateTime;
        $this->typeResolver          = $typeResolver;
    }

    /**
     * @param Participate $participate
     */
    public function handle(Participate $participate)
    {
        // Create a new sheet for this event
        $sheet = new Sheet($participate->event, $participate->type, [], $participate->user, $this->dateTime);

        $this->typeResolver->resolve($participate->user, $participate->event, $participate->type);

        $sheetData       = [];
        $participantData = [];
        $templateData    = $participate->templateData;

        foreach ($participate->data as $key => $value) {
            if ($templateData->getBlock(intval(1))->getObject($key)->hasTag(Tag::PARTICIPANT_DATA)) {
                $participantData = array_merge($participantData, [$key => $value]);
            }

            if ($templateData->getBlock(intval(1))->getObject($key)->hasTag(Tag::SHEET_DATA)) {
                $sheetData = array_merge($sheetData, [$key => $value]);
            }

            $templateData->getBlock(intval(1))->getObject($key)->setData($value);
        }

        $sheet->setRegistrationData($sheetData);
        $this->sheetRepository->add($sheet);

        // Create a new participant
        $participant = new Participant($sheet, $participate->user, $participantData, true);
        $this->participantRepository->add($participant);

        $participate->sheet       = $sheet;
        $participate->participant = $participant;

        $this->accountSynchronizer->set($templateData, $participant->getUser());
    }
}
