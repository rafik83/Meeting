<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\View\Normalizer\EventMeetingsNormalizerView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventMeetingsNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_MEETING_ID              = 'meeting_id';
    const COL_FROM_SHEET_ID           = 'from_sheet_id';
    const COL_FROM_SHEET_NAME         = 'from_sheet_name';
    const COL_FROM_SHEET_PARTICIPANTS = 'from_sheet_participants';
    const COL_TO_SHEET_ID             = 'to_sheet_id';
    const COL_TO_SHEET_NAME           = 'to_sheet_name';
    const COL_TO_SHEET_PARTICIPANTS   = 'to_sheet_participants';
    const COL_DAY                     = 'day';
    const COL_HOUR_BEGIN              = 'hour_begin';
    const COL_HOUR_END                = 'hour_end';
    const COL_SPOT                    = 'spot';
    const EXPORT_BASE_KEY = 'admin.meeting.export.fields.';

    /**
     * @var string
     */
    protected $normalizerType = 'meeting';

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @param TranslatorInterface        $translator
     * @param MeetingRepositoryInterface $meetingRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     */
    public function __construct(
        TranslatorInterface $translator,
        MeetingRepositoryInterface $meetingRepository,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        parent::__construct($translator);

        $this->meetingRepository = $meetingRepository;
        $this->sheetInfoGuesser  = $sheetInfoGuesser;
    }

    /**
     * Normalizes an event's sheets for serialization
     *
     * {@inheritdoc}
     *
     * @param EventMeetingsNormalizerView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $rawMeetings        = [];
        $normalizedMeetings = [];

        foreach ($this->meetingRepository->getAllByEvent($object->event) as $meeting) {
            $rawMeetings[] = $this->getMeetingRawData($meeting, $context['locale']);
        }

        $charset = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;

        foreach ($rawMeetings as $rawMeeting) {
            $normalizedMeetings[] = $this->normalizeMeetingRawData($rawMeeting, $charset);
        }

        return $normalizedMeetings;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EventMeetingsNormalizerView && 'csv' === $format;
    }

    /**
     * @param Meeting $meeting
     * @param string  $locale
     *
     * @return array Raw data about meeting
     */
    private function getMeetingRawData(Meeting $meeting, $locale)
    {
        $event           = $meeting->getFromSheet()->getEvent();
        $availableLocale = $event->getAvailableLocale($locale);

        $timeFormatter = \IntlDateFormatter::create(
            $availableLocale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $event->getTimeZone()
        );

        $dayFormatter = \IntlDateFormatter::create(
            $availableLocale,
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            $event->getTimeZone()
        );

        $sheetRequesterName    = $this->sheetInfoGuesser->guessSheetTitle($meeting->getFromSheet());
        $sheetRequestedName    = $this->sheetInfoGuesser->guessSheetTitle($meeting->getToSheet());
        $fromSheetParticipants = $meeting->getFromParticipants()->toArray();
        $toSheetParticipants   = $meeting->getToParticipants()->toArray();

        $rawData = [
            self::COL_MEETING_ID              => $meeting->getId(),
            self::COL_FROM_SHEET_ID           => $meeting->getFromSheet()->getId(),
            self::COL_FROM_SHEET_NAME         => $sheetRequesterName,
            self::COL_FROM_SHEET_PARTICIPANTS => $this->getParticipantsRawData($fromSheetParticipants),
            self::COL_TO_SHEET_ID             => $meeting->getToSheet()->getId(),
            self::COL_TO_SHEET_NAME           => $sheetRequestedName,
            self::COL_TO_SHEET_PARTICIPANTS   => $this->getParticipantsRawData($toSheetParticipants),
            self::COL_DAY                     => $dayFormatter->format($meeting->getSlot()->getBegin()),
            self::COL_HOUR_BEGIN              => $timeFormatter->format($meeting->getSlot()->getBegin()),
            self::COL_HOUR_END                => $timeFormatter->format($meeting->getSlot()->getEnd()),
            self::COL_SPOT                    => $meeting->getSpot()->getReference(),
        ];

        return $rawData;
    }

    /**
     * @param array $participants
     *
     * @return string
     */
    public function getParticipantsRawData(array $participants)
    {
        return implode(',',
            array_map(
                function (Participant $participant) {
                    return $participant->getId();
                },
                $participants
            )
        );
    }

    /**
     * Returns an array of normalized data from a meeting's raw data
     *
     * @param array  $rawData
     * @param string $charset
     *
     * @return array
     */
    private function normalizeMeetingRawData($rawData, $charset = Charset::WINDOWS_1252)
    {
        $normalizedData = [];

        foreach (self::getCommonFieldKeys() as $fieldKey) {
            $translationKey = self::EXPORT_BASE_KEY . $fieldKey;
            $input          = $rawData[$fieldKey];

            $translatedFieldname = $this->convertCharset(
                $this->translator->trans($translationKey),
                Charset::UTF_8,
                $charset
            );

            $normalizedData[$translatedFieldname] = $this->convertCharset(
                $input,
                Charset::UTF_8,
                $charset
            );
        }

        return $normalizedData;
    }

    /**
     * @return string[] Keys of common columns' headers
     */
    private static function getCommonFieldKeys()
    {
        return [
            self::COL_MEETING_ID,
            self::COL_FROM_SHEET_ID,
            self::COL_FROM_SHEET_NAME,
            self::COL_FROM_SHEET_PARTICIPANTS,
            self::COL_TO_SHEET_ID,
            self::COL_TO_SHEET_NAME,
            self::COL_TO_SHEET_PARTICIPANTS,
            self::COL_DAY,
            self::COL_HOUR_BEGIN,
            self::COL_HOUR_END,
            self::COL_SPOT,
        ];
    }
}
