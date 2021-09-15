<?php

namespace Proximum\Vimeet\Application\Command\MeetingRequest\Admin;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingParticipateEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingUnParticipateEvent;
use Proximum\Vimeet\Application\Exception\MeetingRequest\InvalidParticipantException;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateParticipantsHandler
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /**
     * @param RequestRepositoryInterface     $requestRepository
     * @param ParticipantRepositoryInterface $participantRepository
     * @param DelayedEventDispatcher         $eventDispatcher
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        ParticipantRepositoryInterface $participantRepository,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->requestRepository     = $requestRepository;
        $this->participantRepository = $participantRepository;
        $this->eventDispatcher       = $eventDispatcher;
    }

    /**
     * @param UpdateParticipants $updateParticipants
     *
     * @throws InvalidParticipantException
     */
    public function handle(UpdateParticipants $updateParticipants)
    {
        $fromParticipants = $this->participantRepository->findByIds($updateParticipants->fromParticipants);
        $toParticipants   = $this->participantRepository->findByIds($updateParticipants->toParticipants);

        foreach ($fromParticipants as $fromParticipant) {
            if ($fromParticipant->getSheet() !== $updateParticipants->request->getFromSheet()) {
                throw new InvalidParticipantException(sprintf(
                    'this participant %s is not present on the %s sheet',
                    $fromParticipant->getId(),
                    $updateParticipants->request->getFromSheet()->getId()
                ));
            }
        }

        foreach ($toParticipants as $toParticipant) {
            if ($toParticipant->getSheet() !== $updateParticipants->request->getToSheet()) {
                throw new InvalidParticipantException(sprintf(
                    'this participant %s is not present on the %s sheet',
                    $toParticipant->getId(),
                    $updateParticipants->request->getToSheet()->getId()
                ));
            }
        }

        $oldFromParticipants = $updateParticipants->request->getFromParticipantsArray();
        $oldToParticipants   = $updateParticipants->request->getToParticipantsArray();

        $updateParticipants->request->updateFromParticipants($fromParticipants);
        $updateParticipants->request->updateToParticipants($toParticipants);

        $this->requestRepository->set($updateParticipants->request);

        $oldParticipants = array_merge($oldFromParticipants, $oldToParticipants);
        $newParticipants = array_merge($fromParticipants, $toParticipants);

        foreach ($newParticipants as $newParticipant) {
            if (!in_array($newParticipant, $oldParticipants)) {
                $this->eventDispatcher->dispatch(
                    Events::MEETING_PARTICIPATE,
                    new MeetingParticipateEvent($newParticipant)
                );
            }
        }

        foreach ($oldParticipants as $oldParticipant) {
            if (!in_array($oldParticipant, $newParticipants)) {
                $this->eventDispatcher->dispatch(
                    Events::MEETING_UN_PARTICIPATE,
                    new MeetingUnParticipateEvent($oldParticipant)
                );
            }
        }
    }
}
