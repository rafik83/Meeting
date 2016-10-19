<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetSubmittedEvent;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class SubmitValidationHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var DateTimeInterface
     */
    private $datetime;

    /**
     * SubmitValidationHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param AdminRepositoryInterface $adminRepository
     * @param SheetInfoGuesser         $sheetInfoGuesser
     * @param DelayedEventDispatcher   $eventDispatcher
     * @param DateTimeInterface        $datetime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        AdminRepositoryInterface $adminRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        DelayedEventDispatcher $eventDispatcher,
        DateTimeInterface $datetime
    ) {
        $this->sheetRepository  = $sheetRepository;
        $this->adminRepository  = $adminRepository;
        $this->eventDispatcher  = $eventDispatcher;
        $this->datetime         = $datetime;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param SubmitValidation $command
     */
    public function handle(SubmitValidation $command)
    {
        // put sheet to validation
        $command->sheet->submitToValidation();
        $this->sheetRepository->set($command->sheet);

        // notify sheet's follower
        $this->eventDispatcher->dispatch(
            Events::SHEET_VALIDATION_PENDING,
            new SheetSubmittedEvent(
                $command->sheet,
                $command->user,
                $command->locale
            )
        );
    }
}
