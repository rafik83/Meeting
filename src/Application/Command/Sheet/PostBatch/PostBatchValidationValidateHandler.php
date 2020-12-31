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
    private $datetime;

    /**
     * PostBatchValidationValidateHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $datetime
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime        = $datetime;
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
                    $this->datetime
                )
            );
        }
    }
}
