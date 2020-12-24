<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\OwnerChangedEvent;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ChangeOwnerHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var TranslatorInterface */
    private $translator;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        TranslatorInterface $translator,
        DelayedEventDispatcherInterface $eventDispatcher
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(ChangeOwner $command): void
    {
        $previousOwnerParticipant = $command->sheet->getParticipantOwner();
        $previousOwner = $command->sheet->getOwner();

        if ($previousOwnerParticipant === $command->owner) {
            return;
        }

        $traceComment = $this->translator->trans('admin.sheet.trace.sheet_owner_changed.comment', [
            '%previousOwner%' => $previousOwnerParticipant !== null ?
                $this->participantInfoGuesser->guessParticipantCompleteName($previousOwnerParticipant, $command->locale)
                : sprintf('%s %s', $previousOwner->getFirstName(), $previousOwner->getLastName()),
            '%newOwner%' => $this->participantInfoGuesser->guessParticipantCompleteName($command->owner, $command->locale)
        ]);

        $command->sheet->changeOwner($command->owner->getUser());
        $this->sheetRepository->set($command->sheet);

        $this->eventDispatcher->dispatch(
            Events::SHEET_OWNER_CHANGED,
            new OwnerChangedEvent($command->sheet, $command->admin, $previousOwner, $traceComment)
        );
    }
}
