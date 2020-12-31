<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Meeting\MeetingParticipantView;
use Proximum\Vimeet\Application\View\Meeting\MeetingSheetListView;
use Proximum\Vimeet\Application\View\Meeting\MeetingSheetView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MeetingSheetNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_EVENT_NAME = 'event_name';
    const COL_SHEET_NAME = 'sheet_name';
    const COL_SHEET_TYPE = 'sheet_type';
    const COL_PARTICIPANT_EMAIL = 'participant_email';
    const COL_PARTICIPANT_GENRE = 'participant_genre';
    const COL_PARTICIPANT_FIRSTNAME = 'participant_firstname';
    const COL_PARTICIPANT_LASTNAME = 'participant_lastname';
    const COL_PARTICIPANT_POSITION = 'participant_position';
    const COL_PARTICIPANT_PHONE = 'participant_phone';
    const COL_CONTACT_COMMENT = 'contact_comment';
    const COL_CONTACT_EVALUATION = 'contact_evaluation';
    const COL_SHEET_CATEGORY = 'sheet_category';
    const COL_SHEET_TURNOVER = 'sheet_turnover';
    const COL_SHEET_EMPLOYEES = 'sheet_employees';
    const COL_SHEET_WEBSITE = 'sheet_website';
    const COL_SHEET_ADDRESS = 'sheet_address';
    const COL_SHEET_ZIPCODE = 'sheet_zipcode';
    const COL_SHEET_CITY = 'sheet_city';
    const COL_SHEET_COUNTRY = 'sheet_country';
    const COL_HAS_APPROVED_MEETING_REQUEST_WITH = 'has_approved_meeting_request_with';

    /**
     * {@inheritdoc}
     *
     * @param MeetingSheetListView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedData = [];
        $charset        = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;

        foreach ($object->sheets as $sheet) {
            foreach ($sheet->participants as $participant) {
                $input = $this->getSheetRawData($sheet, $participant, $object->eventName);

                $normalizedData[] = $this->normalizeRawData($input, $charset);
            }
        }

        return $normalizedData;
    }

    /**
     * @param array  $rawData
     * @param string $charset
     *
     * @return array
     */
    public function normalizeRawData(
        array $rawData,
        $charset = Charset::WINDOWS_1252
    ) {
        $normalizedData = [];

        foreach (self::getCommonFieldsKey() as $fieldKey) {
            $translationKey = 'event.sheet.export.fields.' . $fieldKey;
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
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MeetingSheetListView && 'csv' === $format;
    }

    /**
     * @return array
     */
    private static function getCommonFieldsKey()
    {
        return [
            self::COL_EVENT_NAME,
            self::COL_SHEET_NAME,
            self::COL_SHEET_TYPE,
            self::COL_PARTICIPANT_EMAIL,
            self::COL_PARTICIPANT_GENRE,
            self::COL_PARTICIPANT_FIRSTNAME,
            self::COL_PARTICIPANT_LASTNAME,
            self::COL_PARTICIPANT_POSITION,
            self::COL_PARTICIPANT_PHONE,
            self::COL_CONTACT_COMMENT,
            self::COL_CONTACT_EVALUATION,
            self::COL_SHEET_CATEGORY,
            self::COL_SHEET_TURNOVER,
            self::COL_SHEET_EMPLOYEES,
            self::COL_SHEET_WEBSITE,
            self::COL_SHEET_ADDRESS,
            self::COL_SHEET_ZIPCODE,
            self::COL_SHEET_CITY,
            self::COL_SHEET_COUNTRY,
            self::COL_HAS_APPROVED_MEETING_REQUEST_WITH,
        ];
    }

    private function getSheetRawData(
        MeetingSheetView $meetingSheetView,
        MeetingParticipantView $participantView,
        string $eventName
    ): array {
        return [
            self::COL_EVENT_NAME => $eventName,
            self::COL_SHEET_NAME => $meetingSheetView->sheetName,
            self::COL_SHEET_TYPE => $meetingSheetView->type,
            self::COL_PARTICIPANT_EMAIL => $participantView->email,
            self::COL_PARTICIPANT_GENRE => $participantView->gender,
            self::COL_PARTICIPANT_FIRSTNAME => $participantView->firstname,
            self::COL_PARTICIPANT_LASTNAME => $participantView->lastname,
            self::COL_PARTICIPANT_POSITION => $participantView->position,
            self::COL_PARTICIPANT_PHONE => $participantView->phone,
            self::COL_CONTACT_COMMENT => $participantView->comment,
            self::COL_CONTACT_EVALUATION => $participantView->evaluation,
            self::COL_SHEET_CATEGORY => $meetingSheetView->category,
            self::COL_SHEET_TURNOVER => $meetingSheetView->turnover,
            self::COL_SHEET_EMPLOYEES => $meetingSheetView->employees,
            self::COL_SHEET_WEBSITE => $meetingSheetView->website,
            self::COL_SHEET_ADDRESS => $meetingSheetView->address,
            self::COL_SHEET_ZIPCODE => $meetingSheetView->zipcode,
            self::COL_SHEET_CITY => $meetingSheetView->city,
            self::COL_SHEET_COUNTRY => $meetingSheetView->country,
            self::COL_HAS_APPROVED_MEETING_REQUEST_WITH => $this->translator->trans(
                'event.sheet.export.value.' . ($meetingSheetView->hasApprovedMeetingRequestWith ? 'true' : 'false')
            ),
        ];
    }
}
