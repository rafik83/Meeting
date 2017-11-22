<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class AgendaConfirmationStatusQueryHandler
{
    const PREFIX_TRANS_KEY = 'admin.sheet.details.participant.';
    const AGENDA_CONFIRMED = [
        'translation_key' => self::PREFIX_TRANS_KEY . 'agenda_confirmed',
        'css_class'       => 'success'
    ];

    const AGENDA_NOT_CONFIRMED = [
        'translation_key' => self::PREFIX_TRANS_KEY . 'agenda_not_confirmed',
        'css_class'       => 'warning'
    ];

    const CONFIRMATION_NOT_SENT = [
        'translation_key' => self::PREFIX_TRANS_KEY . 'agenda_confirmation_not_send',
        'css_class'       => 'danger'
    ];

    const USER_NOT_CONCERNED = [];

    /** @var UserEventTokenRepositoryInterface */
    private $userEventTokenRepository;

    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /**
     * AgendaConfirmationStatusQueryHandler constructor.
     *
     * @param UserEventTokenRepositoryInterface         $userEventTokenRepository
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param MeetingRepositoryInterface                $meetingRepository
     */
    public function __construct(
        UserEventTokenRepositoryInterface $userEventTokenRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->userEventTokenRepository = $userEventTokenRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->meetingRepository = $meetingRepository;
    }

    /**
     * @param AgendaConfirmationStatusQuery $query
     *
     * @return array ['translation_key' => '', 'css_class' => '']
     */
    public function handle(AgendaConfirmationStatusQuery $query): array
    {
        $user           = $query->participant->getUser();
        $userEventToken = $this->userEventTokenRepository->findByEventAndUserAndType(
            $query->event,
            $user,
            UserEventTokenType::AGENDA_CONFIRMATION
        );

        if (null !== $userEventToken) {
            return $userEventToken->isConfirmed() ? self::AGENDA_CONFIRMED : self::AGENDA_NOT_CONFIRMED;
        }

        if (null !== $this->happeningParticipationRepository->checkAnyParticipation($user, $query->event)
            || false !== $this->meetingRepository->hasScheduledMeetingByParticipant($query->participant)
        ) {
            return self::CONFIRMATION_NOT_SENT;
        }

        return self::USER_NOT_CONCERNED;
    }
}
