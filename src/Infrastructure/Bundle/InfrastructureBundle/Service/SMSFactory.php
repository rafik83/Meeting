<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event;
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
                'event.sms.reminder.pending_meeting_request',
                [
                    '%eventTitle%'                  => $sheet->getEvent()->getTitle(),
                    '%countPendingMeetingRequest%'  => $countPendingMeetingRequest,
                    '%meetingRequestManagementUrl%' => $meetingRequestManagementUrl,
                ],
                'messages',
                $locale
            );

        return new SMS($phone, $message);
    }

    /**
     * @param string      $phone
     * @param Meeting     $meeting
     * @param Sheet       $mySheet
     * @param Sheet       $sheetMet
     * @param Participant $participant
     * @param string      $locale
     *
     * @return SMS
     */
    public function createSentMeetingRequestApproved(
        string $phone,
        Meeting $meeting,
        Sheet $mySheet,
        Sheet $sheetMet,
        Participant $participant,
        string $locale
    ): SMS {
        $link = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $meeting->getEvent(),
            'event_agenda_participant',
            [
                'sheet'       => $mySheet->getId(),
                'participant' => $participant->getId(),
            ]
        );

        $dayFormatter = $this->getDayFormatter($meeting->getEvent(), $locale);

        $timeFormatter = $this->getTimeFormatter($meeting->getEvent(), $locale);

        $message = $this->translator->trans('event.sms.receive_meeting_request.approved', [
            '%event%' => $meeting->getEvent()->getTitle(),
            '%sheet%' => $sheetMet->getTitle(),
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
     * @param Sheet       $mySheet
     * @param Sheet       $sheetMet
     * @param Participant $participant
     * @param string      $locale
     *
     * @return SMS
     */
    public function createReceiveMeetingRequestApproved(
        string $phone,
        Meeting $meeting,
        Sheet $mySheet,
        Sheet $sheetMet,
        Participant $participant,
        string $locale
    ): SMS {
        $link = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $meeting->getEvent(),
            'event_agenda_participant',
            [
                'sheet'       => $mySheet->getId(),
                'participant' => $participant->getId(),
            ]
        );

        $dayFormatter = $this->getDayFormatter($meeting->getEvent(), $locale);

        $timeFormatter = $this->getTimeFormatter($meeting->getEvent(), $locale);

        $message = $this->translator->trans('event.sms.sent_meeting_request.approved', [
            '%event%' => $meeting->getEvent()->getTitle(),
            '%sheet%' => $sheetMet->getTitle(),
            '%date%'  => $dayFormatter->format($meeting->getSlot()->getBegin()),
            '%time%'  => $timeFormatter->format($meeting->getSlot()->getBegin()),
            '%spot%'  => $meeting->getSpot()->getReference(),
            '%link%'  => $link,
        ], 'messages', $locale);

        return new SMS($phone, $message);
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return \IntlDateFormatter
     */
    private function getDayFormatter(Event $event, string $locale): \IntlDateFormatter
    {
        $dayFormatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $event->getTimeZone()
        );

        return $dayFormatter;
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return \IntlDateFormatter
     */
    private function getTimeFormatter(Event $event, string $locale): \IntlDateFormatter
    {
        $timeFormatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $event->getTimeZone()
        );

        return $timeFormatter;
    }
}
