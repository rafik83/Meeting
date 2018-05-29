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

    /** @var MailerInterface */
    private $mailer;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var string */
    private $sender;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        EventDispatcherInterface $eventDispatcher,
        MailerInterface $mailer,
        \DateTimeInterface $datetime,
        string $sender
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime = $datetime;
        $this->mailer = $mailer;
        $this->sender = $sender;
    }

    public function handle(SheetDuplicator $command): void
    {
        foreach ($command->sheets as $sheet) {
            $sheet = Sheet::duplicateSheetFrom($sheet, $command->type, $this->datetime);

            $this->sheetRepository->add($sheet);
            $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, new SheetUpdatedEvent($sheet));
        }

        $this->mailer->send(
            new SheetsDuplicatedMail(
                $command->sheets,
                $command->type->getEvent(),
                $this->sender,
                $command->admin->getEmail(),
                $command->admin->getLocale()
            )
        );
    }
}
