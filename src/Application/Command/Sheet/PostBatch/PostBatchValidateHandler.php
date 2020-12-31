<?php

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidatedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchValidateHandler
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
     * @param EventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface       $dateTime
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, \DateTimeInterface $dateTime)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime        = $dateTime;
    }

    /**
     * @param PostBatchValidate $command
     */
    public function handle(PostBatchValidate $command)
    {
        foreach ($command->sheets as $sheet) {
            $this->eventDispatcher->dispatch(
                Events::SHEET_VALIDATED,
                new SheetValidatedEvent(
                    $sheet,
                    $this->dateTime,
                    $command->comment,
                    $command->admin
                )
            );
        }
    }
}
