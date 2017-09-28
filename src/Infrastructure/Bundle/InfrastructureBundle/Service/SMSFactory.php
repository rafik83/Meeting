<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;

class SMSFactory
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EventUrlGeneratorInterface
     */
    private $eventUrlGenerator;

    /**
     * @param EventUrlGeneratorInterface $eventUrlGenerator
     * @param TranslatorInterface        $translator
     */
    public function __construct(EventUrlGeneratorInterface $eventUrlGenerator, TranslatorInterface $translator)
    {
        $this->translator        = $translator;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    /**
     * @param string $phone
     * @param Sheet  $sheet
     *
     * @return SMS
     */
    public function createMeetingRequestReceive(string $phone, Sheet $sheet): SMS
    {
        $link = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $sheet->getEvent(),
            'event_meeting_list_request',
            [
                'sheet' => $sheet->getId(),
                'state' => Meeting\Constant::FILTER_STATE_RECEIVE,
            ]
        );

        $message = $this->translator->trans('event.sms.meeting_request.receive', [
            '%event%' => $sheet->getEvent()->getTitle(),
            '%link%'  => $link,
        ]);

        return new SMS($phone, $message);
    }

    /**
     * @param string $phone
     * @param Sheet  $sheet
     * @param string $locale
     * @param int    $countPendingMeetingRequest
     *
     * @return SMS
     */
    public function createPendingProposition(
        string $phone,
        Sheet $sheet,
        string $locale,
        int $countPendingMeetingRequest
    ): SMS {
        $meetingRequestManagementUrl = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $sheet->getEvent(),
            'event_meeting_list_request',
            array_merge(
                ['sheet' => $sheet->getId()],
                ['state' => 'receive'],
                ['_locale' => $locale]
            )
        );

        $message = $this
            ->translator
            ->trans(
                'sms.reminder.pending_meeting_request',
                [
                    '%eventTitle%'                  => $sheet->getEvent()->getTitle(),
                    '%countPendingMeetingRequest%'  => $countPendingMeetingRequest,
                    '%meetingRequestManagementUrl%' => $meetingRequestManagementUrl,
                ],
                null,
                $locale
            );

        return new SMS($phone, $message);
    }
}
