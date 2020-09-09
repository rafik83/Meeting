<?php

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Tip\Event\UpdatedEvent;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class UpdateHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        TipRepositoryInterface $tipRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->tipRepository = $tipRepository;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->dateTime = $dateTime;
    }

    public function handle(Update $command): void
    {
        $tip = $command->tip;
        $previousTypes = $tip->getTypes();

        foreach ($previousTypes as $type) {
            if (!in_array($type, $command->types, true)) {
                $tip->removeType($type);
            }
        }

        foreach ($command->types as $type) {
            if (!in_array($type, $tip->getTypes(), true)) {
                $tip->setType($type);
            }
        }

        foreach ($command->translations as $locale => $translation) {
            $tip->translate($locale, $translation['title'], $translation['content'], $this->dateTime);
        }

        $tip->update(
            $command->title,
            $command->onMeetingManagement,
            $command->onCatalog,
            $command->onPrintPlanning,
            $command->onSheet,
            $command->onAgenda,
            $command->onPackage,
            $command->onContacts,
            $command->onProgram,
            $command->onConfirmationPhone
        );

        $tip->updateConditions(
            $command->display,
            $command->conditionOnOrders,
            $command->conditionIsCompleteSheet,
            $command->conditionIsPhoneConfirmed,
            $command->conditionHasRemainingToPay,
            $command->conditionHasPendingMeetingProposition,
            $command->conditionHasCart
        );

        $this->tipRepository->set($tip);

        $this->delayedEventDispatcher->dispatch(
            Events::TIP_EVENT_UPDATED,
            new UpdatedEvent($tip)
        );
    }
}
