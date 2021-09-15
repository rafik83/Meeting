<?php

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\ParticipateHappeningEvent;
use Proximum\Vimeet\Application\Event\Happening\UnParticipateHappeningEvent;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningWithProductToBuyChecker;
use Proximum\Vimeet\Domain\Happening\ParticipationCount;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\View\Happening\HappeningParticipationView;

/**
 * Update Participants to Happening when product are attributed or removed to participant(s)
 */
class UpdateParticipationHandler
{
    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipateToHappeningWithProductToBuyChecker */
    private $participateToHappeningWithProductToBuyChecker;

    /** @var ParticipationCount */
    private $participationCount;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        ParticipantRepositoryInterface $participantRepository,
        ParticipateToHappeningWithProductToBuyChecker $participateToHappeningWithProductToBuyChecker,
        ParticipationCount $participationCount,
        DelayedEventDispatcherInterface $eventDispatcher
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->participantRepository = $participantRepository;
        $this->participateToHappeningWithProductToBuyChecker = $participateToHappeningWithProductToBuyChecker;
        $this->participationCount = $participationCount;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(UpdateParticipation $updateParticipation): HappeningParticipationView
    {
        $participants = $updateParticipation->participants;

        $previousParticipants = $this->participantRepository->getParticipantsForHappening(
            $updateParticipation->sheet,
            $updateParticipation->happening
        );

        // Add previous participants to participants list
        foreach ($previousParticipants as $previousParticipant) {
            if (!\in_array($previousParticipant, $participants, true)) {
                $participants[] = $previousParticipant;
            }
        }

        $availableParticipants = $this->getAvailableAndCanParticipateParticipantsForHappening(
            $participants,
            $updateParticipation->happening
        );

        $removedParticipants = $this->removeParticipantsFromHappening(
            $availableParticipants,
            $previousParticipants,
            $updateParticipation->happening
        );

        $participantsToHappening = $this->addParticipantsToHappening(
            $availableParticipants,
            $previousParticipants,
            $updateParticipation->happening
        );

        $newlyParticipantsAdded = [];
        foreach ($participantsToHappening as $participant) {
            if (!\in_array($participant, $previousParticipants, true)) {
                $newlyParticipantsAdded[] = $participant;
            }
        }

        return new HappeningParticipationView(
            $updateParticipation->happening,
            $newlyParticipantsAdded,
            $removedParticipants
        );
    }

    /**
     * @param Participant[] $availableParticipants
     * @param Participant[] $previousParticipants
     * @param Happening     $happening
     *
     * @return Participant[]
     */
    private function addParticipantsToHappening(
        array $availableParticipants,
        array $previousParticipants,
        Happening $happening
    ): array {
        if ($happening->isPrivate() || 0 === \count($availableParticipants)) {
            return [];
        }

        $remainingParticipations = $this->participationCount->getRemaining($happening);

        if (\count($availableParticipants) - \count($previousParticipants) > $remainingParticipations) {
            return [];
        }

        // Add participants to happening
        foreach ($availableParticipants as $participant) {
            if (true === \in_array($participant, $previousParticipants, true)) {
                continue;
            }

            $this->addOrUpdateParticipation($happening, $participant);
        }

        return $availableParticipants;
    }

    private function addOrUpdateParticipation(Happening $happening, Participant $participant): void
    {
        $happeningParticipation = $this->happeningParticipationRepository->findByHappeningAndUser(
            $happening,
            $participant->getUser()
        );

        if ($happeningParticipation instanceof HappeningParticipation) {
            $happeningParticipation->setDisabled(false);
            $this->happeningParticipationRepository->update($happeningParticipation);
        } else {
            $this->happeningParticipationRepository->add(
                new HappeningParticipation($happening, $participant->getUser())
            );
        }

        $this->eventDispatcher->dispatch(
            Events::HAPPENING_PARTICIPATE,
            new ParticipateHappeningEvent($participant, $happening, true)
        );
    }

    /**
     * @param Participant[] $participants
     * @param Participant[] $previousParticipants
     * @param Happening     $happening
     *
     * @return Participant[]
     */
    private function removeParticipantsFromHappening(
        array $participants,
        array $previousParticipants,
        Happening $happening
    ): array {
        $removedParticipants = [];

        foreach ($previousParticipants as $participant) {
            if (true === \in_array($participant, $participants, true)) {
                continue;
            }

            $this->happeningParticipationRepository->removeUserForHappening(
                $participant->getUser(),
                $happening
            );

            $this->eventDispatcher->dispatch(
                Events::HAPPENING_UN_PARTICIPATE,
                new UnParticipateHappeningEvent($participant, $happening, true)
            );

            $removedParticipants[] = $participant;
        }

        return $removedParticipants;
    }

    /**
     * @param Participant[] $participants
     * @param Happening     $happening
     *
     * @return array
     */
    private function getAvailableAndCanParticipateParticipantsForHappening(
        array $participants,
        Happening $happening
    ): array {
        if (0 === \count($participants)) {
            return [];
        }

        $availableParticipants = $this->participantRepository->getAvailableParticipantsForHappening(
            $participants,
            $happening
        );

        $availableAndCanParticipateParticipants = [];

        foreach ($availableParticipants as $participant) {
            if ($this->participateToHappeningWithProductToBuyChecker->canParticipate($participant, $happening)) {
                $availableAndCanParticipateParticipants[] = $participant;
            }
        }

        return $availableAndCanParticipateParticipants;
    }
}
