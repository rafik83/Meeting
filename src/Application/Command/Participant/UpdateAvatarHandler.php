<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateAvatarHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var Synchronizer
     */
    private $accountSynchronizer;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param Synchronizer                   $accountSynchronizer
     * @param DelayedEventDispatcher         $eventDispatcher
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        Synchronizer $accountSynchronizer,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->participantRepository = $participantRepository;
        $this->accountSynchronizer   = $accountSynchronizer;
        $this->eventDispatcher       = $eventDispatcher;
    }

    /**
     * @param UpdateAvatar $updateAvatar
     */
    public function handle(UpdateAvatar $updateAvatar)
    {
        $participant = $updateAvatar->participant;
        $participant->setData($updateAvatar->templateData->getData());

        $this->participantRepository->set($participant);

        if ($participant->getUser() === $updateAvatar->user) {
            $this->accountSynchronizer->set($updateAvatar->templateData, $participant->getUser());
        }

        // Send Sheet Update Event to recalculate completeness of the sheet
        $sheetUpdatedEvent = new SheetUpdatedEvent($participant->getSheet());
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent);
    }
}
