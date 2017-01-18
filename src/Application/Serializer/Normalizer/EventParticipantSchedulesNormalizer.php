<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer;


use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Nomenclature\Charset;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\View\Normalizer\EventParticipantSchedulesNormalizerView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventParticipantSchedulesNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_PARTICIPANT_ID      = 'participantId';
    const COL_TITLE               = 'title';
    const COL_FIRSTNAME           = 'firstName';
    const COL_LASTNAME            = 'lastName';
    const COL_COMPANY             = 'companyName';
    const COL_PARTICIPATION_TYPE  = 'type';
    const COL_DESCRIPTION         = 'description';
    const COL_POSITION            = 'position';
    const COL_PHONE_PREFIX        = 'phonePrefix';
    const COL_PHONE_NUMBER        = 'phoneNumber';
    const COL_EMAIL               = 'email';
    const COL_MOBILE_PHONE_PREFIX = 'mobilePhonePrefix';
    const COL_MOBILE_PHONE        = 'mobilePhone';
    const COL_SCHEDULE            = 'schedule';

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /**
     * @param TranslatorInterface            $translator
     * @param ParticipantRepositoryInterface $participantRepository
     * @param ParticipantInfoGuesser         $participantInfoGuesser
     * @param SheetInfoGuesser               $sheetInfoGuesser
     */
    public function __construct(
        TranslatorInterface $translator,
        ParticipantRepositoryInterface $participantRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        parent::__construct($translator);

        $this->participantRepository  = $participantRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
    }

    /**
     * Normalizes participants' schedules for a given event for serialization.
     *
     * {@inheritdoc}
     *
     * @param EventParticipantSchedulesNormalizerView $object
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $rawParticipants = [];
        $locale          = $context['locale'];

        foreach ($this->participantRepository->getParticipantsByEvent($object->event, $locale) as $participant) {
            $rawParticipants[] = $this->getParticipantRawData($participant, $locale);
        }

        $charset                = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;
        $normalizedParticipants = [];

        foreach ($rawParticipants as $rawParticipant) {
            $normalizedParticipants[] = $this->normalizeParticipantRawData($rawParticipant, $charset);
        }

        return $normalizedParticipants;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'csv' === $format && $data instanceof EventParticipantSchedulesNormalizerView;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return array Raw data about participant's schedule
     */
    private function getParticipantRawData(Participant $participant, $locale)
    {
        $sheet           = $participant->getSheet();
        $event           = $sheet->getEvent();
        $locale          = $event->getAvailableLocale($locale);
        $participantInfo = $this->participantInfoGuesser->guessParticipantInfos($participant, $locale);

        $gender = isset($participantInfo[Tag::PARTICIPANT_GENDER]) ? $participantInfo[Tag::PARTICIPANT_GENDER] : null;
        if ($gender) {
            $gender = $this->translator->trans(sprintf('gender.%s', $gender));
        }

        return [
            self::COL_PARTICIPANT_ID      => sprintf("%s-%s", $sheet->getId(), $participant->getId()),
            self::COL_TITLE               => $gender,
            self::COL_FIRSTNAME           => isset($participantInfo[Tag::PARTICIPANT_FIRSTNAME]) ? $participantInfo[Tag::PARTICIPANT_FIRSTNAME] : null,
            self::COL_LASTNAME            => isset($participantInfo[Tag::PARTICIPANT_LASTNAME]) ? $participantInfo[Tag::PARTICIPANT_LASTNAME] : null,
            self::COL_COMPANY             => $this->sheetInfoGuesser->guessSheetName($sheet, $locale),
            self::COL_PARTICIPATION_TYPE  => $sheet->getType()->getTitle($locale),
            self::COL_DESCRIPTION         => null,
            self::COL_POSITION            => isset($participantInfo[Tag::PARTICIPANT_POSITION]) ? $participantInfo[Tag::PARTICIPANT_POSITION] : null,
            self::COL_PHONE_PREFIX        => null,
            self::COL_PHONE_NUMBER        => isset($participantInfo[Tag::PARTICIPANT_PHONE]) ? $participantInfo[Tag::PARTICIPANT_PHONE] : null,
            self::COL_EMAIL               => $participant->getUser()->getEmail(),
            self::COL_MOBILE_PHONE_PREFIX => null,
            self::COL_MOBILE_PHONE        => isset($participantInfo[Tag::PARTICIPANT_MOBILE]) ? $participantInfo[Tag::PARTICIPANT_MOBILE] : null,
            self::COL_SCHEDULE            => null,
        ];
    }

    /**
     * Returns an array of normalized data from a participant's schedule raw data
     * (normalizing includes charset encoding, string substitution for boolean values, etc.)
     *
     * @param array  $rawData
     * @param string $charset
     *
     * @return array
     */
    private function normalizeParticipantRawData($rawData, $charset = Charset::WINDOWS_1252)
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
}
