<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\MeetingRequest;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\MeetingRequest\Export\MeetingRequestListView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MeetingRequestListViewNormalizer implements NormalizerInterface
{
    const TRANSLATION_COL_PREFIX   = 'export.meeting_request.col.';
    const TRANSLATION_STATE_PREFIX = 'export.meeting_request.state.';
    const TRANSLATION_DOMAIN       = 'export';

    const COL_REQUEST_ID             = 'requestId';
    const COL_MEETING_ID             = 'meetingId';
    const COL_FROM_SHEET_ID          = 'fromSheetId';
    const COL_FROM_SHEET_TYPE        = 'fromSheetType';
    const COL_FROM_SHEET_CATEGORY    = 'fromSheetCategory';
    const COL_FROM_SHEET_TITLE       = 'fromSheetTitle';
    const COL_FROM_PARTICIPANT_IDS   = 'fromParticipantIds';
    const COL_FROM_PARTICIPANT_NAMES = 'fromParticipantNames';
    const COL_TO_SHEET_ID            = 'toSheetId';
    const COL_TO_SHEET_TITLE         = 'toSheetTitle';
    const COL_TO_SHEET_TYPE          = 'toSheetType';
    const COL_TO_SHEET_CATEGORY      = 'toSheetCategory';
    const COL_TO_PARTICIPANT_IDS     = 'toParticipantIds';
    const COL_TO_PARTICIPANT_NAMES   = 'toParticipantNames';
    const COL_REQUEST_STATE          = 'state';
    const COL_REQUEST_CREATED_AT     = 'createdAt';
    const COL_REQUEST_UPDATED_AT     = 'updatedAt';
    const COL_CREATED_TYPE           = 'createdType';
    const COL_SLOT                   = 'slot';

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var MeetingRequestListView $meetingRequestListView */
        $meetingRequestListView = $object;

        $data = [];

        $locale = $meetingRequestListView->locale;
        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::SHORT,
            $meetingRequestListView->timeZone
        );

        foreach ($meetingRequestListView->meetingRequests as $meetingRequest) {
            $createdType = $meetingRequest->createdType ? $this->convertCharset($this->translator->trans(self::TRANSLATION_COL_PREFIX . self::COL_CREATED_TYPE . '.' . $meetingRequest->createdType, [], self::TRANSLATION_DOMAIN, $locale)) : null;

            $data[] = [
                $this->colTrans(self::COL_REQUEST_ID, $locale)             => $meetingRequest->id,
                $this->colTrans(self::COL_MEETING_ID, $locale)             => $meetingRequest->meetingId,
                $this->colTrans(self::COL_FROM_SHEET_ID, $locale)          => $meetingRequest->fromSheet->id,
                $this->colTrans(self::COL_FROM_SHEET_TITLE, $locale)       => $this->convertCharset($meetingRequest->fromSheet->title),
                $this->colTrans(self::COL_FROM_SHEET_TYPE, $locale)        => $this->convertCharset($meetingRequest->fromSheet->typeTitle),
                $this->colTrans(self::COL_FROM_SHEET_CATEGORY, $locale)    => $this->convertCharset($meetingRequest->fromSheet->categoryTitle),
                $this->colTrans(self::COL_FROM_PARTICIPANT_IDS, $locale)   => implode(',', $meetingRequest->fromSheet->participantIds),
                $this->colTrans(self::COL_FROM_PARTICIPANT_NAMES, $locale) => implode(',', $meetingRequest->fromSheet->participantNames),
                $this->colTrans(self::COL_TO_SHEET_ID, $locale)            => $meetingRequest->toSheet->id,
                $this->colTrans(self::COL_TO_SHEET_TITLE, $locale)         => $this->convertCharset($meetingRequest->toSheet->title),
                $this->colTrans(self::COL_TO_SHEET_TYPE, $locale)          => $this->convertCharset($meetingRequest->toSheet->typeTitle),
                $this->colTrans(self::COL_TO_SHEET_CATEGORY, $locale)      => $this->convertCharset($meetingRequest->toSheet->categoryTitle),
                $this->colTrans(self::COL_TO_PARTICIPANT_IDS, $locale)     => implode(',', $meetingRequest->toSheet->participantIds),
                $this->colTrans(self::COL_TO_PARTICIPANT_NAMES, $locale)   => implode(',', $meetingRequest->toSheet->participantNames),
                $this->colTrans(self::COL_REQUEST_STATE, $locale)          => $this->convertCharset($this->translator->trans(self::TRANSLATION_STATE_PREFIX . $meetingRequest->state, [], self::TRANSLATION_DOMAIN, $locale)),
                $this->colTrans(self::COL_REQUEST_CREATED_AT, $locale)     => $this->formatDate($dateFormatter, $meetingRequest->createdAt),
                $this->colTrans(self::COL_REQUEST_UPDATED_AT, $locale)     => $this->formatDate($dateFormatter, $meetingRequest->updatedAt),
                $this->colTrans(self::COL_CREATED_TYPE, $locale)           => $createdType,
                $this->colTrans(self::COL_SLOT, $locale)                   => null !== $meetingRequest->slotBeginDate ? $this->formatDate($dateFormatter, $meetingRequest->slotBeginDate) : null
            ];
        }

        return $data;
    }

    /**
     * @param \IntlDateFormatter $dateFormatter
     * @param \DateTimeInterface $date
     *
     * @return string
     */
    private function formatDate(\IntlDateFormatter $dateFormatter, \DateTimeInterface $date): string
    {
        $formattedDate = $dateFormatter->format($date);

        return false !== $formattedDate ? $formattedDate : '';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof MeetingRequestListView && 'csv' === $format;
    }

    /**
     * @param string $colName
     * @param string $locale
     *
     * @return string
     */
    private function colTrans(string $colName, string $locale): string
    {
        return $this->convertCharset(
            $this->translator->trans(self::TRANSLATION_COL_PREFIX . $colName, [], self::TRANSLATION_DOMAIN, $locale)
        );
    }

    /**
     * @param null|string $input
     *
     * @return string
     */
    private function convertCharset(?string $input): string
    {
        return iconv(Charset::UTF_8, Charset::WINDOWS_1252 . '//TRANSLIT//IGNORE', $input);
    }
}
