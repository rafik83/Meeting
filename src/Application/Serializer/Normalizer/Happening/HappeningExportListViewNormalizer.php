<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Happening;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningExportListView;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningExportView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HappeningExportListViewNormalizer implements NormalizerInterface
{
    private const COL_TITLE = 'title';
    private const COL_DESCRIPTION = 'description';
    private const COL_CATEGORY = 'category';
    private const COL_BEGIN = 'begin';
    private const COL_END = 'end';
    private const COL_PARTICIPANT_SCANNED = 'participant scanned';
    private const COL_NUMBER_OF_GRADES = 'number of grades';
    private const COL_AVERAGE_GRADES = 'average grades';
    private const COL_SPEAKER_NAME_PREFIX = 'speaker name ';
    private const COL_SPEAKER_POSITION_PREFIX = 'speaker position ';
    private const COL_SPEAKER_SOCIETY_PREFIX = 'speaker society ';
    private const COL_SPEAKER_AVATAR_PREFIX = 'speaker avatar url ';
    private const COL_SPEAKER_LOGO_PREFIX = 'speaker logo url ';

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof HappeningExportListView && 'csv' === $format;
    }

    /**
     * {@inheritdoc}
     *
     * @param HappeningExportListView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedUserRawData = [];
        $charset = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;

        foreach ($object->getHappeningExportListView() as $happeningExportView) {
            $normalizedUserRawData[] = $this->normalizeUserRawData(
                $this->getHappeningExportViewRawData($happeningExportView),
                $charset
            );
        }

        return $normalizedUserRawData;
    }

    private function normalizeUserRawData(array $rawData, string $charset): array
    {
        $normalizedData = [];

        foreach ($rawData as $fieldKey => $input) {
            $normalizedData[$fieldKey] = $this->convertCharset(
                $input,
                Charset::UTF_8,
                $charset
            );
        }

        return $normalizedData;
    }

    private function getHappeningExportViewRawData(HappeningExportView $happeningExportView): array
    {
        $data = [
            self::COL_TITLE => $happeningExportView->getTitle(),
            self::COL_DESCRIPTION => $happeningExportView->getDescription(),
            self::COL_CATEGORY => $happeningExportView->getCategory(),
            self::COL_BEGIN => $happeningExportView->getBegin(),
            self::COL_END => $happeningExportView->getEnd(),
            self::COL_PARTICIPANT_SCANNED => $happeningExportView->getParticipantScanned(),
            self::COL_NUMBER_OF_GRADES => $happeningExportView->getNumberOfGrades(),
            self::COL_AVERAGE_GRADES => $happeningExportView->getAverageGrades(),
        ];

        foreach ($happeningExportView->speakersListView->getSpeakersListView() as $i => $speakerExportView) {
            $data = array_merge(
                $data,
                [
                    self::COL_SPEAKER_NAME_PREFIX . $i => $speakerExportView->getName(),
                    self::COL_SPEAKER_POSITION_PREFIX . $i => $speakerExportView->getPosition(),
                    self::COL_SPEAKER_SOCIETY_PREFIX . $i => $speakerExportView->getSociety(),
                    self::COL_SPEAKER_AVATAR_PREFIX . $i => $speakerExportView->getUrlAvatar(),
                    self::COL_SPEAKER_LOGO_PREFIX . $i => $speakerExportView->getUrlLogo(),
                ]
            );
        }

        return $data;
    }

    protected function convertCharset($input, string $inCharset = Charset::UTF_8, string $outCharset = Charset::WINDOWS_1252)
    {
        if (!$input || !is_string($input)) {
            return $input;
        }

        if ($inCharset !== $outCharset) {
            return iconv($inCharset, $outCharset . '//TRANSLIT//IGNORE', $input);
        }

        return $input;
    }
}
