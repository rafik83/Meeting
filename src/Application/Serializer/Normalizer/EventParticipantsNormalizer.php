<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Order\PromotionCode;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ExportableObjectInterface;
use Proximum\Vimeet\Domain\View\Normalizer\EventParticipantsNormalizerView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventParticipantsNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_SHEET_ID                     = 'sheet_id';
    const COL_SHEET_NAME                   = 'sheet_name';
    const COL_PARTICIPANT_TYPE             = 'participant_type';
    const COL_PARTICIPANT_ID               = 'participant_id';
    const COL_PARTICIPANT_EMAIL            = 'participant_email';
    const COL_PARTICIPANT_CREATED_AT       = 'participant_created_at';
    const COL_HAPPENING_SUBSCRIBER         = 'happening_subscriber';
    const COL_PARTICIPANT_ORDER_PROMO_CODE = 'participant_order_promo_code';

    /**
     * @var string
     */
    protected $normalizerType = 'participant';

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * List of all registration fields (merging of the registration data fields of all normalized sheets)
     *
     * @var array (key => label)
     */
    private $registrationFields;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var HappeningParticipationRepositoryInterface
     */
    private $happeningParticipationRepository;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * Order merger
     *
     * @var Merger
     */
    private $merger;

    /**
     * @param TranslatorInterface                       $translator
     * @param TemplateDataFactory                       $templateDataFactory
     * @param ParticipantRepositoryInterface            $participantRepository
     * @param SheetInfoGuesser                          $sheetInfoGuesser
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param Merger                                    $merger
     */
    public function __construct(
        TranslatorInterface $translator,
        TemplateDataFactory $templateDataFactory,
        ParticipantRepositoryInterface $participantRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        Merger $merger
    ) {
        parent::__construct($translator);

        $this->participantRepository            = $participantRepository;
        $this->registrationFields               = [];
        $this->sheetInfoGuesser                 = $sheetInfoGuesser;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->templateDataFactory              = $templateDataFactory;
        $this->merger                           = $merger;
    }

    /**
     * Normalizes an event's sheets for serialization
     *
     * {@inheritdoc}
     *
     * @param EventParticipantsNormalizerView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $rawSheets = [];
        $locale    = $context['locale'];

        foreach ($this->participantRepository->getParticipantsByEvent($object->event, $locale) as $participant) {
            $rawSheets[] = $this->getParticipantRawData($participant, $locale);
        }

        $charset = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;
        $normalizedParticipants = [];

        foreach ($rawSheets as $rawSheet) {
            $normalizedParticipants[] = $this->normalizeParticipantRawData($rawSheet, $charset);
        }

        return $normalizedParticipants;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EventParticipantsNormalizerView && 'csv' === $format;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return array Raw data about sheet
     */
    private function getParticipantRawData(Participant $participant, $locale)
    {
        $event = $participant->getSheet()->getEvent();

        $availableLocale = $event->getAvailableLocale($locale);
        $fallbackLocale  = $event->getFallback();
        $sheet           = $participant->getSheet();

        $timeFormatter = \IntlDateFormatter::create(
            $availableLocale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $event->getTimeZone()
        );

        $promotionCodes = [];

        if ($sheet->hasOrders()) {
            $order = $this->merger->merge($sheet->getOrders());
            foreach($order->getPromotionCodes() as $orderPromotionCode) {
                $promotionCodes[] = $orderPromotionCode->getPromotionCode()->getCode();
            }
        }

        // 1. Common fields (sheet ID, participant ID, etc.)
        $rawData = [
            self::COL_SHEET_ID                     => $sheet->getId(),
            self::COL_PARTICIPANT_TYPE             => $sheet->getType()->getTitle($availableLocale),
            self::COL_SHEET_NAME                   => $this->sheetInfoGuesser->guessSheetTitle($sheet, $availableLocale),
            self::COL_PARTICIPANT_ID               => $participant->getId(),
            self::COL_PARTICIPANT_EMAIL            => $participant->getUser()->getEmail(),
            self::COL_PARTICIPANT_CREATED_AT       => $timeFormatter->format($sheet->getCreatedAt()),
            self::COL_HAPPENING_SUBSCRIBER         => $this->getHappeningSubscriberData($participant),
            self::COL_PARTICIPANT_ORDER_PROMO_CODE => implode(',', $promotionCodes),
        ];

        // 2. Registration data
        $this->addRegistrationRawData($rawData, $participant, $availableLocale, $fallbackLocale);

        return $rawData;
    }

    /**
     * @param array       $rawData
     * @param Participant $participant
     * @param string      $availableLocale
     * @param string      $fallbackLocale
     */
    private function addRegistrationRawData(&$rawData, Participant $participant, $availableLocale, $fallbackLocale)
    {
        $registrationTemplateData = $this
            ->templateDataFactory
            ->createRegistrationFromParticipant($participant, $availableLocale);

        foreach ($registrationTemplateData->getProfileObjects() as $registrationObject) {
            if ($registrationObject instanceof ExportableObjectInterface) {
                $key = $registrationObject->getKey();

                if (!isset($this->registrationFields[$key])) {
                    $fieldName = $registrationObject->getExportableFieldname(
                        $availableLocale,
                        $fallbackLocale
                    );

                    $this->registrationFields[$key] = $fieldName;
                }

                $rawData[$key] = $registrationObject->getExportableContent();
            }
        }
    }

    /**
     * Returns an array of normalized data from a participant's raw data
     * (normalizing includes encoding, common field headers' translations, adding missing columns, etc.)
     *
     * @param array  $rawData
     * @param string $charset
     *
     * @return array
     */
    private function normalizeParticipantRawData($rawData, $charset = Charset::WINDOWS_1252)
    {
        $normalizedData = [];

        // Common fields (event ID, event name, etc.)
        foreach (self::getCommonFieldKeys() as $fieldKey) {
            $translationKey = 'admin.participant.export.fields.' . $fieldKey;
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

        // Registration data
        foreach ($this->registrationFields as $fieldKey => $fieldName) {
            $input                      = isset($rawData[$fieldKey]) ? $rawData[$fieldKey] : null;
            $fieldName                  = $this->convertCharset($fieldName, Charset::UTF_8, $charset);
            $normalizedData[$fieldName] = $this->normalizeInput($input, Charset::UTF_8, $charset);
        }

        return $normalizedData;
    }

    /**
     * @return string[] Keys of common columns' headers
     */
    private static function getCommonFieldKeys()
    {
        return [
            self::COL_SHEET_ID,
            self::COL_PARTICIPANT_TYPE,
            self::COL_SHEET_NAME,
            self::COL_PARTICIPANT_ID,
            self::COL_PARTICIPANT_EMAIL,
            self::COL_PARTICIPANT_CREATED_AT,
            self::COL_HAPPENING_SUBSCRIBER,
            self::COL_PARTICIPANT_ORDER_PROMO_CODE,
        ];
    }

    /**
     * @param Participant $participant
     *
     * @return string
     */
    private function getHappeningSubscriberData(Participant $participant)
    {
        $transKeyHappeningSubscriber = 'admin.participant.export.happening.subscriber.';

        $happeningSubscriber = $this
            ->happeningParticipationRepository
            ->checkAnyParticipation($participant) ? 'yes' : 'no';

        return $this->translator->trans($transKeyHappeningSubscriber . $happeningSubscriber);
    }
}
