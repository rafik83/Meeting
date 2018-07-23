<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\Details;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Meeting\Admin\Details\MeetingView;
use Proximum\Vimeet\Application\View\Meeting\Admin\Details\ParticipantView;
use Proximum\Vimeet\Application\View\Meeting\Admin\Details\SheetView;
use Proximum\Vimeet\Application\View\Meeting\Admin\Details\SlotView;
use Proximum\Vimeet\Application\View\Meeting\Admin\Details\SpotView;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

class MeetingViewQueryHandler
{
    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var UserEventPhoneChecker */
    private $userEventPhoneChecker;

    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        UserEventPhoneChecker $userEventPhoneChecker
    ) {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->userEventPhoneChecker = $userEventPhoneChecker;
    }

    /**
     * @param MeetingViewQuery $meetingViewQuery
     *
     * @return MeetingView
     */
    public function handle(MeetingViewQuery $meetingViewQuery)
    {
        $meeting = $meetingViewQuery->meeting;
        $locale  = $meetingViewQuery->locale;

        $fromSheet = new SheetView(
            $meeting->getFromSheet()->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($meeting->getFromSheet(), $locale)
        );

        $toSheet = new SheetView(
            $meeting->getToSheet()->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($meeting->getToSheet(), $locale)
        );

        $fromParticipants = [];
        $toParticipants   = [];

        foreach ($meeting->getFromParticipants()->toArray() as $fromParticipant) {
            $userEventPhone = $this->userEventPhoneChecker
                ->getValidatedUserEventPhone($fromParticipant->getUser(), $meeting->getEvent());

            $fromParticipants[] = new ParticipantView(
                $this->participantInfoGuesser->guessParticipantCompleteName($fromParticipant, $locale),
                $userEventPhone instanceof UserEventPhone ? $userEventPhone->getPhone() : null
            );
        }

        foreach ($meeting->getToParticipants()->toArray() as $toParticipant) {
            $userEventPhone = $this->userEventPhoneChecker
                ->getValidatedUserEventPhone($toParticipant->getUser(), $meeting->getEvent());

            $toParticipants[] = new ParticipantView(
                $this->participantInfoGuesser->guessParticipantCompleteName($toParticipant, $locale),
                $userEventPhone instanceof UserEventPhone ? $userEventPhone->getPhone() : null
            );
        }

        $spot = new SpotView($meeting->getSpot()->getReference());
        $slot = new SlotView($meeting->getSlot()->getBegin(), $meeting->getSlot()->getEnd());

        return new MeetingView(
            $meeting->getId(),
            $meeting->getRequest()->getId(),
            $fromSheet,
            $fromParticipants,
            $toSheet,
            $toParticipants,
            $spot,
            $slot,
            $meeting->getCreatedAt()
        );
    }
}
