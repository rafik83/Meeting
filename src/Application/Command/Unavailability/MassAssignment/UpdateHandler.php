<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\MassAssignment;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Mass\Assignment\AssignmentUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOnMeetingException;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOutOfMassSlotException;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateHandler
{
    /**
     * @var MassAssignmentRepositoryInterface
     */
    private $massAssignmentRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /**
     * UpdateHandler constructor.
     *
     * @param MassAssignmentRepositoryInterface $massAssignmentRepository
     * @param ParticipantRepositoryInterface    $participantRepository
     * @param DelayedEventDispatcher            $eventDispatcher
     */
    public function __construct(
        MassAssignmentRepositoryInterface $massAssignmentRepository,
        ParticipantRepositoryInterface $participantRepository,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->massAssignmentRepository = $massAssignmentRepository;
        $this->participantRepository    = $participantRepository;
        $this->eventDispatcher          = $eventDispatcher;
    }

    /**
     * @param Update $update
     *
     * @throws MassAssignmentOnMeetingException
     * @throws MassAssignmentOutOfMassSlotException
     */
    public function handle(Update $update)
    {
        $participants = $this->participantRepository->getAllParticipantForUser(
            $update->massAssignment->getMass()->getEvent(),
            $update->massAssignment->getUser()
        );

        // if set to disable, only update enabled state
        if (false === $update->enabled) {
            $update->massAssignment->disable();
            $this->massAssignmentRepository->set($update->massAssignment);

            foreach ($participants as $participant) {
                $this->dispatchUpdateEvent($participant);
            }

            return;
        }

        $availableParticipants = $this->participantRepository->getAvailableParticipants(
            $participants,
            $update->begin,
            $update->end
        );

        // If there is at least a participant that is not available, the mass assignment can not be changed
        if (count($availableParticipants) !== count($participants)) {
            throw new MassAssignmentOnMeetingException();
        }

        if (($update->begin < $update->massAssignment->getMass()->getBegin()
                || $update->begin > $update->massAssignment->getMass()->getEnd())
            ||
            ($update->end > $update->massAssignment->getMass()->getEnd()
                || $update->end < $update->massAssignment->getMass()->getBegin())
            ||
            ($update->begin >= $update->end)
        ) {
            throw new MassAssignmentOutOfMassSlotException();
        }

        $update->massAssignment->update(
            $update->begin,
            $update->end,
            $update->enabled
        );

        $this->massAssignmentRepository->set($update->massAssignment);

        foreach ($participants as $participant) {
            $this->dispatchUpdateEvent($participant);
        }
    }

    /**
     * @param $participant
     */
    private function dispatchUpdateEvent($participant)
    {
        $this->eventDispatcher->dispatch(
            Events::MASS_ASSIGNMENT_UPDATED,
            new AssignmentUpdatedEvent($participant)
        );
    }
}
