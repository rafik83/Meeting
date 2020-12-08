<?php

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Tip\Event\CreatedEvent;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class CreateHandler
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

    public function handle(Create $command): void
    {
        $tip = new Tip(
            $command->title,
            $command->event,
            $command->onMeetingManagement,
            $command->onCatalog,
            $command->onPrintPlanning,
            $command->onSheet,
            $command->onAgenda,
            $command->onPackage,
            $command->onContacts,
            $command->onProgram,
            $command->onConfirmationPhone,
            $command->onNetworking,
            $this->dateTime
        );

        foreach ($command->translations as $locale => $translation) {
            $tip->translate($locale, $translation['title'], $translation['content'], $this->dateTime);
        }

        foreach ($command->types as $type) {
            $tip->setType($type);
        }

        $tip->updateConditions(
            $command->display,
            $command->conditionOnOrders,
            $command->conditionIsCompleteSheet,
            $command->conditionIsPhoneConfirmed,
            $command->conditionHasRemainingToPay,
            $command->conditionHasPendingMeetingProposition,
            $command->conditionHasCart
        );

        $this->tipRepository->add($tip);

        $this->delayedEventDispatcher->dispatch(
            Events::TIP_EVENT_CREATED,
            new CreatedEvent($tip)
        );
    }
}
