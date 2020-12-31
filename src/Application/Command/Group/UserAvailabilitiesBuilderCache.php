<?php

namespace Proximum\Vimeet\Application\Command\Group;

use Proximum\Vimeet\Application\View\Sheet\Group\Participant\AgendaDayView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UserAvailabilitiesBuilderCache
{
    /** @var AgendaDayView[] */
    private $dayViews = [];

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository
    ) {
        $this->participantRepository = $participantRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param User            $user
     * @param Event           $event
     * @param AgendaDayView[] $skeletonDayViews
     *
     * @return AgendaDayView[]
     */
    public function buildAvailabilitiesByUserAndEventFromSkeleton(User $user, Event $event, array $skeletonDayViews)
    {
        if (isset($this->dayViews[$user->getId()])) {
            return $this->dayViews[$user->getId()];
        }

        $dayViews = $this->setUserAvailabilities(
            $skeletonDayViews,
            $this->getSlotsByUserAndEvent($user, $event)
        );

        return $this->dayViews[$user->getId()] = $dayViews;
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return MeetingSlot[]
     */
    private function getSlotsByUserAndEvent(User $user, Event $event)
    {
        $participants = $this->participantRepository->getParticipantsByUserForEvent($user->getId(), $event);

        if (empty($participants)) {
            return [];
        }

        return $this->meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            $participants,
            true
        );
    }

    /**
     * @param AgendaDayView[] $skeletonDayViews
     * @param MeetingSlot[]   $userSlots
     *
     * @return AgendaDayView[]
     */
    private function setUserAvailabilities(array $skeletonDayViews, array $userSlots)
    {
        foreach ($userSlots as $userSlot) {
            foreach ($skeletonDayViews as $dayView) {
                foreach ($dayView->slotViews as $slot) {
                    if ($slot->begin === $userSlot->getBegin()) {
                        $slot->available = true;
                    }
                }
            }
        }

        return $skeletonDayViews;
    }
}
