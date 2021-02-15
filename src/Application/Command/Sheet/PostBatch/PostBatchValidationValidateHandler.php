<?php

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidationValidateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchValidationValidateHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * PostBatchValidationValidateHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $dateTime
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime        = $dateTime;
    }

    /**
     * @param PostBatchValidationValidate $command
     */
    public function handle(PostBatchValidationValidate $command)
    {
        foreach ($command->sheets as $sheet) {
            $this->eventDispatcher->dispatch(
                Events::SHEET_VALIDATION_VALIDATE,
                new SheetValidationValidateEvent(
                    $sheet,
                    $command->admin,
                    $this->dateTime
                )
            );
        }
    }
}
