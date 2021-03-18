<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Participant\Export;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantListView;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ParticipantListViewNormalizer implements NormalizerInterface
{
    public const COL_SHEET_ID = 'sheet_id';
    public const COL_SHEET_NAME = 'sheet_name';
    public const COL_SHEET_ENABLE = 'sheet_enable';
    public const COL_PARTICIPANT_TYPE = 'participant_type';
    public const COL_USER_ID = 'user_id';
    public const COL_PARTICIPANT_ID = 'participant_id';
    public const COL_PARTICIPANT_EMAIL = 'participant_email';
    public const COL_PARTICIPANT_CREATED_AT = 'participant_created_at';
    public const COL_HAPPENING_SUBSCRIBER = 'happening_subscriber';
    public const COL_PARTICIPATION_PAID = 'participation_paid';
    public const COL_VIEWED_SHEETS = 'viewed_sheets';
    public const COL_CLICKED_ELEMENTS = 'clicked_elements';
    public const COL_REQUESTED_MEETINGS = 'requested_meetings';
    public const COL_SCHEDULED_MEETINGS = 'scheduled_meetings';
    public const COL_CHAT_SESSIONS_CALL_VISIO = 'chat_sessions_call_visio';
    public const COL_PARTICIPANT_LOCAL = 'participant_locale';
    public const TRANSLATION_KEY = 'admin.participant.export.fields.';

    public const COMMON_COL = [
        self::COL_SHEET_ID,
        self::COL_PARTICIPANT_TYPE,
        self::COL_SHEET_NAME,
        self::COL_SHEET_ENABLE,
        self::COL_USER_ID,
        self::COL_PARTICIPANT_ID,
        self::COL_PARTICIPANT_EMAIL,
        self::COL_PARTICIPANT_CREATED_AT,
        self::COL_HAPPENING_SUBSCRIBER,
        self::COL_PARTICIPATION_PAID,
        self::COL_VIEWED_SHEETS,
        self::COL_CLICKED_ELEMENTS,
        self::COL_REQUESTED_MEETINGS,
        self::COL_SCHEDULED_MEETINGS,
        self::COL_CHAT_SESSIONS_CALL_VISIO,
        self::COL_PARTICIPANT_LOCAL,
    ];

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var ParticipantListView $participantListView */
        $participantListView = $object;

        $result = [];
        $firstLine = [];

        foreach (self::COMMON_COL as $col) {
            $firstLine[$col] = $this->convertCharset(
                $this->translator->trans(
                    sprintf('%s%s', self::TRANSLATION_KEY, $col),
                    [],
                    null,
                    $participantListView->locale
                )
            );
        }

        foreach ($participantListView->registrationColumns as $key => $registrationColumn) {
            $firstLine[$key] = $this->convertCharset($registrationColumn);
        }

        foreach ($participantListView->dayColumns as $key => $dayColumn) {
            $firstLine[$key] = $this->convertCharset(
                $this->translator->trans(
                    'admin.participant.export.fields.day_checkin',
                    ['%date%' => $dayColumn],
                    null,
                    $participantListView->locale
                )
            );
        }

        foreach ($participantListView->happeningColumns as $key => $happeningColumns) {
            $firstLine[$key] = $this->convertCharset($happeningColumns);
        }

        foreach ($participantListView->productColumns as $key => $productColumn) {
            $firstLine[$key] = $this->convertCharset($productColumn);
        }

        $result[] = $firstLine;

        foreach ($participantListView->participantViews as $participantView) {
            $result[] = $this->handleParticipantView($participantListView, $participantView);
        }

        return $result;
    }

    /**
     * @param mixed $input
     *
     * @return mixed|string
     */
    private function convertCharset($input)
    {
        return Charset::convertString($input);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ParticipantListView && 'csv' === $format;
    }

    private function handleParticipantView(ParticipantListView $participantListView, ParticipantView $participantView): array
    {
        $data = [
            self::COL_SHEET_ID => $participantView->sheetId,
            self::COL_PARTICIPANT_TYPE => $this->convertCharset($participantView->typeTitle),
            self::COL_SHEET_NAME => $this->convertCharset($participantView->sheetTitle),
            self::COL_SHEET_ENABLE => $this->convertCharset($this->translator->trans(
                sprintf('admin.participant.export.%s', $participantView->sheetEnabled ? 'yes' : 'no'),
                [],
                null,
                $participantListView->locale
            )),
            self::COL_USER_ID => $participantView->userId,
            self::COL_PARTICIPANT_ID => $participantView->participantId,
            self::COL_PARTICIPANT_EMAIL => $participantView->email,
            self::COL_PARTICIPANT_CREATED_AT => $participantView->createdAt,
            self::COL_HAPPENING_SUBSCRIBER => $this->convertCharset(
                $this->translator->trans(
                    sprintf('admin.participant.export.%s', $participantView->hasHappeningParticipation ? 'yes' : 'no'),
                    [],
                    null,
                    $participantListView->locale
                )
            ),
            self::COL_PARTICIPATION_PAID => $this->convertCharset(
                $this->translator->trans(
                    sprintf(
                        '%s%s.%s',
                        self::TRANSLATION_KEY,
                        self::COL_PARTICIPATION_PAID,
                        $participantView->hasPaidParticipation ? 'paid' : 'not_paid'
                    ),
                    [],
                    null,
                    $participantListView->locale
                )
            ),
            self::COL_VIEWED_SHEETS => $participantView->viewedSheets,
            self::COL_CLICKED_ELEMENTS => $participantView->clickedElements,
            self::COL_REQUESTED_MEETINGS => $participantView->requestedMeetings,
            self::COL_SCHEDULED_MEETINGS => $participantView->scheduledMeetings,
            self::COL_CHAT_SESSIONS_CALL_VISIO => $participantView->chatSessionsCallVisio,
            self::COL_PARTICIPANT_LOCAL => $participantView->locale
        ];

        foreach ($participantView->daysChecking as $dayKey => $checkin) {
            $data[$dayKey] = $checkin;
        }

        foreach ($participantView->happeningChecking as $happeningKey => $happening) {
            $data[$happeningKey] = $happening;
        }

        foreach ($participantListView->registrationColumns as $key => $registrationColumn) {
            $data[$key] = isset($participantView->registrationData[$key])
                ? $this->convertCharset($participantView->registrationData[$key])
                : '';
        }

        foreach ($participantListView->productColumns as $productKey => $productColumn) {
            $data[$productKey] = $this->convertCharset(
                $this->translator->trans(
                    sprintf(
                        'admin.participant.export.%s',
                        isset($participantView->attributableProducts[$productKey]) ? 'yes' : 'no'
                    ),
                    [],
                    null,
                    $participantListView->locale
                )
            );
        }

        if (null !== $participantView->participantProductId) {
            $data[sprintf('participant_%s', $participantView->participantProductId)] = $this->convertCharset(
                $this->translator->trans(
                    'admin.participant.export.yes',
                    [],
                    null,
                    $participantListView->locale
                )
            );
        }

        return $data;
    }
}
