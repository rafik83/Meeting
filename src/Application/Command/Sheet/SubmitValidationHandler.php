<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetSubmittedEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class SubmitValidationHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * SubmitValidationHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param DelayedEventDispatcher   $eventDispatcher
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
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
            new SheetSubmittedEvent($command->sheet, $command->user)
        );
    }
}
