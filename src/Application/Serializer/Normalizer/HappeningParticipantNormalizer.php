<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningParticipantListView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HappeningParticipantNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_HAPPENING_ID          = 'happening_id';
    const COL_HAPPENING_NAME        = 'happening_name';
    const COL_HAPPENING_DAY         = 'happening_day';
    const COL_HAPPENING_BEGIN_HOUR  = 'happening_begin_hour';
    const COL_HAPPENING_END_HOUR    = 'happening_end_hour';
    const COL_SHEET_ID              = 'sheet_id';
    const COL_SHEET_NAME            = 'sheet_name';
    const COL_PARTICIPANT_ID        = 'participant_id';
    const COL_PARTICIPANT_EMAIL     = 'participant_email';
    const COL_PARTICIPANT_FIRSTNAME = 'participant_firstname';
    const COL_PARTICIPANT_LASTNAME  = 'participant_lastname';
    const COL_PARTICIPANT_POSITION  = 'participant_position';
    const COL_QUESTION              = 'question';

    protected $normalizerType = 'happening';

    /**
     * {@inheritdoc}
     *
     * @param HappeningParticipantListView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $happeningParticipants = [];

        $charset = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;

        foreach ($object->getHappeningParticipantListView() as $happeningParticipantView) {
            $normalizedData = $this->normalizeRawData(
                $happeningParticipantView->toArray(),
                $charset
            );

            $happeningParticipants[] = $normalizedData;
        }

        return $happeningParticipants;
    }

    /**
     * @param array  $rawData
     * @param string $charset
     *
     * @return array
     */
    public function normalizeRawData(array $rawData, $charset = Charset::WINDOWS_1252)
    {
        $normalizedData = [];

        foreach (self::getCommonFieldKeys() as $fieldKey) {
            $translationKey = 'admin.happening.export.fields.' . $fieldKey;
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
        return $data instanceof HappeningParticipantListView && 'csv' === $format;
    }

    /**
     * @return string[] Keys of common columns' headers
     */
    private static function getCommonFieldKeys()
    {
        return [
            self::COL_HAPPENING_ID,
            self::COL_HAPPENING_BEGIN_HOUR,
            self::COL_HAPPENING_END_HOUR,
            self::COL_HAPPENING_DAY,
            self::COL_HAPPENING_NAME,
            self::COL_SHEET_ID,
            self::COL_SHEET_NAME,
            self::COL_PARTICIPANT_ID,
            self::COL_PARTICIPANT_EMAIL,
            self::COL_PARTICIPANT_FIRSTNAME,
            self::COL_PARTICIPANT_LASTNAME,
            self::COL_PARTICIPANT_POSITION,
            self::COL_QUESTION,
        ];
    }
}
