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
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateCompanyHandler
{
    /**
     * @var Synchronizer
     */
    private $accountSynchronizer;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param Synchronizer             $accountSynchronizer
     * @param DelayedEventDispatcher   $eventDispatcher
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        Synchronizer $accountSynchronizer,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->sheetRepository     = $sheetRepository;
        $this->accountSynchronizer = $accountSynchronizer;
        $this->eventDispatcher     = $eventDispatcher;
    }

    /**
     * @param UpdateCompany $updateCompany
     */
    public function handle(UpdateCompany $updateCompany)
    {
        $participant  = $updateCompany->participant;
        $sheet        = $updateCompany->sheet;
        $companyData  = $sheet->getRegistrationData();
        $templateData = $updateCompany->templateData;

        foreach ($updateCompany->data as $key => $value) {
            if ($templateData->getObject($key)->hasTag(Tag::SHEET_DATA)) {
                $companyData = array_merge($companyData, [$key => $value]);

                // Set the data on the TemplateData to use it with the accountSynchronizer
                $templateData->getObject($key)->setData($value);
            }
        }

        $sheet->setRegistrationData($companyData);

        $this->sheetRepository->set($sheet);

        if ($participant !== null && $participant->getUser() === $updateCompany->user) {
            $this->accountSynchronizer->set($templateData, $updateCompany->participant->getUser());
        }

        // Send Sheet Update Event to recalculate completeness of the sheet
        $sheetUpdatedEvent = new SheetUpdatedEvent($sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent);
    }
}
