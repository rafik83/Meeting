<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Group\GroupDuplicator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\SheetsDuplicatedMail;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetDuplicatorHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var GroupDuplicator */
    private $groupDuplicator;

    /** @var MailerInterface */
    private $mailer;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var string */
    private $sender;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        EventDispatcherInterface $eventDispatcher,
        GroupDuplicator $groupDuplicator,
        MailerInterface $mailer,
        \DateTimeInterface $datetime,
        string $sender
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->groupDuplicator = $groupDuplicator;
        $this->datetime = $datetime;
        $this->mailer = $mailer;
        $this->sender = $sender;
    }

    public function handle(SheetDuplicator $command): void
    {
        $importedSheets = [];
        $destinationEvent = $command->type->getEvent();

        foreach ($command->sheets as $sheet) {
            if (true === $this->sheetRepository->hasSheetBeenDuplicatedByEvent($sheet, $destinationEvent)) {
                continue;
            }

            $group = null;

            if ($sheet->getGroup() instanceof Sheet\Group) {
                $group = $this->groupDuplicator->duplicateToEvent($sheet->getGroup(), $destinationEvent);
            }

            $sheet = Sheet::duplicateSheetFrom($sheet, $group, $command->type, $this->datetime);
            $this->sheetRepository->add($sheet);
            $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, new SheetUpdatedEvent($sheet));

            $importedSheets[] = $sheet;
        }

        if (empty($importedSheets)) {
            return;
        }

        $this->mailer->send(
            new SheetsDuplicatedMail(
                $destinationEvent,
                $command->originEvent,
                $importedSheets,
                $this->sender,
                $command->admin->getEmail(),
                $command->admin->getLocale()
            )
        );
    }
}
