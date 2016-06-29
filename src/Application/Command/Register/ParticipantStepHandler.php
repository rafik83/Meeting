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
use Proximum\Vimeet\Application\Event\Event\PreRegisterEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var Synchronizer
     */
    private $accountSynchronizer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param ParticipantRepositoryInterface $participantRepository
     * @param Synchronizer                   $accountSynchronizer
     * @param EventDispatcherInterface       $eventDispatcher
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        Synchronizer $accountSynchronizer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->participantRepository = $participantRepository;
        $this->accountSynchronizer   = $accountSynchronizer;
        $this->eventDispatcher       = $eventDispatcher;
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
            if ($templateData->getBlock(intval($participantStep->step))->getObject($key)
                             ->hasTag(Tag::PARTICIPANT_DATA)
            ) {
                $participantData = array_merge($participantData, [$key => $value]);
            }

            if ($templateData->getBlock(intval($participantStep->step))->getObject($key)->hasTag(Tag::SHEET_DATA)) {
                $sheetData = array_merge($sheetData, [$key => $value]);
            }

            $templateData->getBlock(intval($participantStep->step))->getObject($key)->setData($value);
        }

        $participantStep->participant->setData($participantData);
        $participantStep->sheet->setRegistrationData($sheetData);

        $this->participantRepository->set($participantStep->participant);
        $this->sheetRepository->set($participantStep->sheet);

        $this->accountSynchronizer->set($templateData, $participantStep->participant->getUser());

        $this->triggerEvent($participantStep);
    }

    /**
     * @param ParticipantStep $participantStep
     */
    private function triggerEvent(ParticipantStep $participantStep)
    {
        if ($participantStep->step == 3) {
            $preRegisteredEvent = new PreRegisterEvent(
                $participantStep->sheet->getEvent(),
                $participantStep->participant->getUser(),
                $participantStep->locale
            );

            $this->eventDispatcher->dispatch('event.preregistered', $preRegisteredEvent);
        }
    }
}
