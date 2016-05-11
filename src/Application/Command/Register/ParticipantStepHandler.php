<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Register;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class ParticipantStepHandler
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
     * @param ParticipantStep $participantStep
     */
    public function handle(ParticipantStep $participantStep)
    {
        $sheetData       = $participantStep->sheet->getRegistrationData();
        $participantData = $participantStep->participant->getData();
        $templateData    = $participantStep->templateData;

        foreach ($participantStep->data as $key => $value) {
            if ($templateData->getBlock(intval($participantStep->step))->getObject($key)->hasTag(Tag::PARTICIPANT_DATA)) {
                $participantData = array_merge($participantData, [$key => $value]);
            }

            if ($templateData->getBlock(intval($participantStep->step))->getObject($key)->hasTag(Tag::SHEET_DATA)) {
                $sheetData = array_merge($sheetData, [$key => $value]);
            }
        }

        $participantStep->participant->setData($participantData);
        $participantStep->sheet->setRegistrationData($sheetData);

        $this->participantRepository->set($participantStep->participant);
        $this->sheetRepository->set($participantStep->sheet);
    }
}
