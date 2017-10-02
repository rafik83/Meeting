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
use Proximum\Vimeet\Domain\Model\Participant;
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
     * @param string $locale
     *
     * @return SMS
     */
    public function createMeetingRequestReceive(string $phone, Sheet $sheet, string $locale): SMS
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
        ], 'messages', $locale);

        return new SMS($phone, $message);
    }

    /**
     * @param string      $phone
     * @param Meeting     $meeting
     * @param Sheet       $fromSheet
     * @param Sheet       $toSheet
     * @param Participant $participant
     * @param string      $locale
     *
     * @return SMS
     */
    public function createReceiveMeetingRequestApproved(
        string $phone,
        Meeting $meeting,
        Sheet $fromSheet,
        Sheet $toSheet,
        Participant $participant,
        string $locale
    ): SMS {
        $link = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $meeting->getEvent(),
            'event_agenda_participant',
            [
                'sheet'       => $fromSheet->getId(),
                'participant' => $participant->getId(),
            ]
        );

        $dayFormatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $meeting->getEvent()->getTimeZone()
        );

        $timeFormatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $meeting->getEvent()->getTimeZone()
        );

        $message = $this->translator->trans('event.sms.receive_meeting_request.approved', [
            '%event%' => $meeting->getEvent()->getTitle(),
            '%sheet%' => $toSheet->getTitle(),
            '%date%'  => $dayFormatter->format($meeting->getSlot()->getBegin()),
            '%time%'  => $timeFormatter->format($meeting->getSlot()->getBegin()),
            '%spot%'  => $meeting->getSpot()->getReference(),
            '%link%'  => $link,
        ], 'messages', $locale);

        return new SMS($phone, $message);
    }

    /**
     * @param string      $phone
     * @param Meeting     $meeting
     * @param Sheet       $fromSheet
     * @param Sheet       $toSheet
     * @param Participant $participant
     * @param string      $locale
     *
     * @return SMS
     */
    public function createSentMeetingRequestApproved(
        string $phone,
        Meeting $meeting,
        Sheet $fromSheet,
        Sheet $toSheet,
        Participant $participant,
        string $locale
    ): SMS {
        $link = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $meeting->getEvent(),
            'event_agenda_participant',
            [
                'sheet'       => $toSheet->getId(),
                'participant' => $participant->getId(),
            ]
        );

        $dayFormatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $meeting->getEvent()->getTimeZone()
        );

        $timeFormatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $meeting->getEvent()->getTimeZone()
        );

        $message = $this->translator->trans('event.sms.sent_meeting_request.approved', [
            '%event%' => $meeting->getEvent()->getTitle(),
            '%sheet%' => $fromSheet->getTitle(),
            '%date%'  => $dayFormatter->format($meeting->getSlot()->getBegin()),
            '%time%'  => $timeFormatter->format($meeting->getSlot()->getBegin()),
            '%spot%'  => $meeting->getSpot()->getReference(),
            '%link%'  => $link,
        ], 'messages', $locale);

        return new SMS($phone, $message);
    }
}
