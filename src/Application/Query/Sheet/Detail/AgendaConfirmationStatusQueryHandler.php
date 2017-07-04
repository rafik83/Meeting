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

class AgendaConfirmationStatusQueryHandler
{
    const PREFIX_TRANS_KEY = 'admin.sheet.details.participant.';
    const AGENDA_CONFIRMED =
        [
            'translation_key' => self::PREFIX_TRANS_KEY . 'agenda_confirmed',
            'css_class'       => 'success'
        ];
    const AGENDA_NOT_CONFIRMED =
        [
            'translation_key' => self::PREFIX_TRANS_KEY . 'agenda_not_confirmed',
            'css_class'       => 'warning'
        ];
    const CONFIRMATION_NOT_SENT =
        [
            'translation_key' => self::PREFIX_TRANS_KEY . 'agenda_confirmation_not_send',
            'css_class'       => 'danger'
        ];
    const USER_NOT_CONCERNED = [];

    /** @var UserEventTokenRepositoryInterface */
    private $userEventTokenRepository;

    /** @var HappeningParticipationRepositoryInterface */
    private $happenningParticipationRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /**
     * AgendaConfirmationStatusQueryHandler constructor.
     *
     * @param UserEventTokenRepositoryInterface         $userEventTokenRepository
     * @param HappeningParticipationRepositoryInterface $happenningParticipationRepository
     * @param MeetingRepositoryInterface                $meetingRepository
     */
    public function __construct(
        UserEventTokenRepositoryInterface $userEventTokenRepository,
        HappeningParticipationRepositoryInterface $happenningParticipationRepository,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->userEventTokenRepository = $userEventTokenRepository;
        $this->happenningParticipationRepository = $happenningParticipationRepository;
        $this->meetingRepository = $meetingRepository;
    }

    /**
     * @param AgendaConfirmationStatusQuery $query
     *
     * @return array ['translation_key' => '', 'css_class' => '']
     */
    public function handle(AgendaConfirmationStatusQuery $query): array
    {
        $user = $query->participant->getUser();

        if (null !== $userEventToken = $this->userEventTokenRepository->getConfirmationStatusByUserAndType($user)) {
            return $userEventToken->isConfirmed() ? self::AGENDA_CONFIRMED : self::AGENDA_NOT_CONFIRMED;
        }

        if (null !== $this->happenningParticipationRepository->checkAnyParticipation($user, $query->event)
            || false !== $this->meetingRepository->hasScheduledMeetingByParticipant($query->participant)
        ) {
            return self::CONFIRMATION_NOT_SENT;
        }

        return self::USER_NOT_CONCERNED;
    }
}
