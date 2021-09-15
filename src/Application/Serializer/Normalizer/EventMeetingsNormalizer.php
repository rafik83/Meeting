<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Planning\ParticipantInfoGuesserCache;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\View\Normalizer\EventMeetingsNormalizerView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventMeetingsNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_MEETING_ID                      = 'meeting_id';
    const COL_FROM_SHEET_ID                   = 'from_sheet_id';
    const COL_FROM_SHEET_TITLE                = 'from_sheet_title';
    const COL_FROM_SHEET_PARTICIPANT_FULLNAME = 'from_sheet_participant_fullname';
    const COL_FROM_SHEET_PARTICIPANTS         = 'from_sheet_participants';
    const COL_FROM_USER_IDS                   = 'from_user_ids';
    const COL_TO_SHEET_ID                     = 'to_sheet_id';
    const COL_TO_SHEET_TITLE                  = 'to_sheet_title';
    const COL_TO_SHEET_PARTICIPANT_FULLNAME   = 'to_sheet_participant_fullname';
    const COL_TO_SHEET_PARTICIPANTS           = 'to_sheet_participants';
    const COL_TO_USER_IDS                     = 'to_user_ids';
    const COL_DAY                             = 'day';
    const COL_HOUR_BEGIN                      = 'hour_begin';
    const COL_HOUR_END                        = 'hour_end';
    const COL_SPOT                            = 'spot';
    const COL_CREATED_TYPE                    = 'created_type';
    const COL_STATUS                          = 'status';
    const EXPORT_BASE_KEY                     = 'admin.meeting.export.fields.';

    /**
     * @var string
     */
    protected $normalizerType = 'meeting';

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var \IntlDateFormatter
     */
    private $dayFormatter;

    /**
     * @var \IntlDateFormatter
     */
    private $timeFormatter;

    /**
     * @var ParticipantInfoGuesserCache
     */
    private $participantInfoGuesserCache;

    /**
     * @param TranslatorInterface         $translator
     * @param MeetingRepositoryInterface  $meetingRepository
     * @param ParticipantInfoGuesserCache $participantInfoGuesserCache
     */
    public function __construct(
        TranslatorInterface $translator,
        MeetingRepositoryInterface $meetingRepository,
        ParticipantInfoGuesserCache $participantInfoGuesserCache
    ) {
        parent::__construct($translator);

        $this->meetingRepository           = $meetingRepository;
        $this->participantInfoGuesserCache = $participantInfoGuesserCache;
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

        $availableLocale = $object->event->getAvailableLocale($context['locale']);

        $this->timeFormatter = \IntlDateFormatter::create(
            $availableLocale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $object->event->getTimeZone()
        );

        $this->dayFormatter = \IntlDateFormatter::create(
            $availableLocale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $object->event->getTimeZone()
        );

        foreach ($this->meetingRepository->getAllCompleteByEvent($object->event) as $meeting) {
            $rawMeetings[] = $this->getMeetingRawData($meeting, $availableLocale);
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
    private function getMeetingRawData($meeting, $locale)
    {
        /** @var Meeting $rawMeeting */
        $rawMeeting            = $meeting['meeting'];
        $fromSheetParticipants = $rawMeeting->getFromParticipants()->toArray();
        $toSheetParticipants   = $rawMeeting->getToParticipants()->toArray();

        $fromUserIds = array_map(function (Participant $participant) {
            return $participant->getUser()->getId();
        }, $fromSheetParticipants);

        $toUserIds = array_map(function (Participant $participant) {
            return $participant->getUser()->getId();
        }, $toSheetParticipants);

        $fromSheetParticipantsFullName = $this->getSheetParticipantsFullName($fromSheetParticipants, $locale);
        $toSheetParticipantsFullName   = $this->getSheetParticipantsFullName($toSheetParticipants, $locale);

        $createdType = $this->translator->trans(sprintf('admin.meeting.list.createdType.%s', $rawMeeting->getCreatedType()));
        $status = $this->translator->trans(sprintf('admin.meeting.list.status.%s', $rawMeeting->getStatus()));

        $rawData = [
            self::COL_MEETING_ID                      => $rawMeeting->getId(),
            self::COL_FROM_SHEET_ID                   => $rawMeeting->getFromSheet()->getId(),
            self::COL_FROM_SHEET_TITLE                => $rawMeeting->getFromSheet()->getTitle(),
            self::COL_FROM_SHEET_PARTICIPANT_FULLNAME => implode(',', $fromSheetParticipantsFullName),
            self::COL_FROM_SHEET_PARTICIPANTS         => $this->getParticipantsRawData($fromSheetParticipants),
            self::COL_FROM_USER_IDS                   => implode(',', $fromUserIds),
            self::COL_TO_SHEET_ID                     => $rawMeeting->getToSheet()->getId(),
            self::COL_TO_SHEET_TITLE                  => $rawMeeting->getToSheet()->getTitle(),
            self::COL_TO_SHEET_PARTICIPANT_FULLNAME   => implode(',', $toSheetParticipantsFullName),
            self::COL_TO_SHEET_PARTICIPANTS           => $this->getParticipantsRawData($toSheetParticipants),
            self::COL_TO_USER_IDS                     => implode(',', $toUserIds),
            self::COL_DAY                             => $this->dayFormatter->format($meeting['meetingBegin']),
            self::COL_HOUR_BEGIN                      => $this->timeFormatter->format($meeting['meetingBegin']),
            self::COL_HOUR_END                        => $this->timeFormatter->format($meeting['meetingEnd']),
            self::COL_SPOT                            => $meeting['spotReference'],
            self::COL_CREATED_TYPE                    => $createdType,
            self::COL_STATUS                          => $status,
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
            self::COL_FROM_SHEET_TITLE,
            self::COL_FROM_SHEET_PARTICIPANT_FULLNAME,
            self::COL_FROM_SHEET_PARTICIPANTS,
            self::COL_FROM_USER_IDS,
            self::COL_TO_SHEET_ID,
            self::COL_TO_SHEET_TITLE,
            self::COL_TO_SHEET_PARTICIPANT_FULLNAME,
            self::COL_TO_SHEET_PARTICIPANTS,
            self::COL_TO_USER_IDS,
            self::COL_DAY,
            self::COL_HOUR_BEGIN,
            self::COL_HOUR_END,
            self::COL_SPOT,
            self::COL_CREATED_TYPE,
            self::COL_STATUS,
        ];
    }

    /**
     * @param array  $sheetParticipants
     * @param string $locale
     *
     * @return array
     */
    private function getSheetParticipantsFullName($sheetParticipants, $locale)
    {
        $fromSheetParticipantFullName = array_map(function (Participant $participant) use ($locale) {
            return $this->participantInfoGuesserCache->guessParticipantCompleteName($participant, $locale);
        }, $sheetParticipants);

        return $fromSheetParticipantFullName;
    }
}
