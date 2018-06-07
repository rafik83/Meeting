<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Participant\Export;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantListView;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ParticipantListViewNormalizer implements NormalizerInterface
{
    public const COL_SHEET_ID               = 'sheet_id';
    public const COL_SHEET_NAME             = 'sheet_name';
    public const COL_SHEET_ENABLE           = 'sheet_enable';
    public const COL_PARTICIPANT_TYPE       = 'participant_type';
    public const COL_USER_ID                = 'user_id';
    public const COL_PARTICIPANT_ID         = 'participant_id';
    public const COL_PARTICIPANT_EMAIL      = 'participant_email';
    public const COL_PARTICIPANT_CREATED_AT = 'participant_created_at';
    public const COL_HAPPENING_SUBSCRIBER   = 'happening_subscriber';
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
    public function normalize($object, $format = null, array $context = array())
    {
        /** @var ParticipantListView $participantListView */
        $participantListView = $object;

        $result = [];
        $firstLine = [];

        foreach (self::COMMON_COL as $col) {
            $firstLine[$col] = $this->convertCharset(
                $this->translator->trans(
                    sprintf('%s%s', self::TRANSLATION_KEY, $col), [], null, $participantListView->locale
                )
            );
        }

        foreach ($participantListView->registrationColumns as $key => $registrationColumn) {
            $firstLine[$key] = $this->convertCharset($registrationColumn);
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
     * @return string
     */
    private function convertCharset($input): string
    {
        if (!$input || !\is_string($input)) {
            return $input;
        }

        return iconv(Charset::UTF_8, Charset::WINDOWS_1252 . '//TRANSLIT//IGNORE', $input);
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
            self::COL_PARTICIPANT_TYPE => $participantView->typeTitle,
            self::COL_SHEET_NAME => $participantView->sheetTitle,
            self::COL_SHEET_ENABLE => $this->translator->trans(
                sprintf('admin.participant.export.%s', $participantView->sheetEnabled ? 'yes' : 'no')
            ),
            self::COL_USER_ID => $participantView->userId,
            self::COL_PARTICIPANT_ID => $participantView->participantId,
            self::COL_PARTICIPANT_EMAIL => $participantView->email,
            self::COL_PARTICIPANT_CREATED_AT => $participantView->createdAt,
            self::COL_HAPPENING_SUBSCRIBER => $this->translator->trans(
                sprintf('admin.participant.export.%s', $participantView->hasHappeningParticipation ? 'yes' : 'no')
            ),
        ];

        foreach ($participantListView->registrationColumns as $key => $registrationColumn) {
            $data[$key] = $participantView->registrationData[$key] ?? '';
        }

        return $data;
    }
}
