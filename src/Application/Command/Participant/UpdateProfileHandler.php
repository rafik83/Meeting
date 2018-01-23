<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetTitleCheckEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateProfileHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var Synchronizer
     */
    private $accountSynchronizer;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param Synchronizer                   $accountSynchronizer
     * @param DelayedEventDispatcher         $eventDispatcher
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        Synchronizer $accountSynchronizer,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->participantRepository = $participantRepository;
        $this->accountSynchronizer   = $accountSynchronizer;
        $this->eventDispatcher       = $eventDispatcher;
    }

    /**
     * @param UpdateProfile $updateProfile
     */
    public function handle(UpdateProfile $updateProfile)
    {
        $participant     = $updateProfile->participant;
        $participantData = $updateProfile->participant->getData();
        $templateData    = $updateProfile->templateData;

        foreach ($updateProfile->data as $key => $value) {
            if ($templateData->getObject($key)->hasTag(Tag::PARTICIPANT_DATA)) {
                $participantData = array_merge($participantData, [$key => $value]);

                // Set the data on the TemplateData to use it with the accountSynchronizer
                $templateData->getObject($key)->setData($value);
            }
        }

        $updateProfile->participant->setData($participantData);

        $this->participantRepository->set($participant);

        if ($participant->getUser() === $updateProfile->user) {
            $this->accountSynchronizer->set($templateData, $updateProfile->participant->getUser());
        }

        // Send Sheet Update Event to recalculate completeness of the sheet
        $sheetUpdatedEvent = new SheetUpdatedEvent($participant->getSheet());

        // Send event to check and update sheet title depends on sheet title or owner fullname settings
        $sheetTitleCheckEvent = new SheetTitleCheckEvent($participant->getSheet());

        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent);
        $this->eventDispatcher->dispatch(Events::SHEET_TITLE_CHECK, $sheetTitleCheckEvent);
    }
}
