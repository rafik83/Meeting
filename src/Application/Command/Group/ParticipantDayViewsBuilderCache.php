<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Group;

use Proximum\Vimeet\Application\View\Sheet\Group\Participant\AgendaDayView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantDayViewsBuilderCache
{
    /** @var array */
    private $dayViews = [];

    /** @var Event */
    private $event;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * ParticipantDayViewsBuilderCache constructor.
     *
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
     * @param User  $user
     * @param Event $event
     * @param AgendaDayView[] $skeletonDayViews
     *
     * @return AgendaDayView[]
     */
    public function buildDayViewsByUserAndEventFromSkeleton(User $user, Event $event, array $skeletonDayViews)
    {
        if (array_key_exists($user->getId(), $this->dayViews)) {
            return $this->dayViews[$user->getId()];
        }

        $userSlots = $this->getSlotsByUserAndEvent($user, $event);
        $dayViews  = $this->setSkeletonDayViewsFromUserAvailableSlots($skeletonDayViews, $userSlots);

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

        $participantIds = array_map(function ($participant) {
            return $participant->getId();
        }, $participants);

        return $this->meetingSlotRepository->findAvailableSlotsByParticipantsIds(
            $event,
            $participantIds,
            true
        );
    }

    /**
     * @param array $skeletonDayViews
     * @param array $userSlots
     *
     * @return AgendaDayView[]
     */
    private function setSkeletonDayViewsFromUserAvailableSlots(array $skeletonDayViews, array $userSlots)
    {
        foreach ($userSlots as $userSlot) {
            foreach($skeletonDayViews as $dayView) {
                foreach ($dayView->slotViews as $slot) {
                    if ($slot->begin === $userSlot->getBegin())  {
                        $slot->available = true;
                    }
                }
            }
        }

        return $skeletonDayViews;
    }
}
