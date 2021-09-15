<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Participant\ParticipantsSheetIdsView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ExportableObjectInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ParticipantsSheetIdsViewNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_SHEET_ID               = 'sheet_id';
    const COL_SHEET_NAME             = 'sheet_name';
    const COL_SHEET_ENABLE           = 'sheet_enable';
    const COL_PARTICIPANT_TYPE       = 'participant_type';
    const COL_USER_ID                = 'user_id';
    const COL_PARTICIPANT_ID         = 'participant_id';
    const COL_PARTICIPANT_EMAIL      = 'participant_email';
    const COL_PARTICIPANT_CREATED_AT = 'participant_created_at';
    const COL_HAPPENING_SUBSCRIBER   = 'happening_subscriber';
    const TRANSLATION_KEY = 'admin.participant.export.fields.';

    const COMMON_COL = [
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

    /** @var string */
    protected $normalizerType = 'participant';

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * List of all registration fields (merging of the registration data fields of all normalized sheets)
     *
     * @var array (key => label)
     */
    private $registrationFields;

    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /**
     * @param TranslatorInterface                       $translator
     * @param TemplateDataFactory                       $templateDataFactory
     * @param ParticipantRepositoryInterface            $participantRepository
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        TemplateDataFactory $templateDataFactory,
        ParticipantRepositoryInterface $participantRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository
    ) {
        parent::__construct($translator);

        $this->participantRepository            = $participantRepository;
        $this->registrationFields               = [];
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->templateDataFactory              = $templateDataFactory;
    }

    /**
     * Normalizes an event's sheets for serialization
     *
     * {@inheritdoc}
     *
     * @param ParticipantsSheetIdsView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $participantsSheetIdsView = $object;
        $rawSheets                = [];
        $event                    = $context['event'];
        $locale                   = $context['locale'];
        $charset                  = $context['charset'];

        $participants = $this->participantRepository->getByEventAndSheetIdsAndLocale(
            $event,
            $participantsSheetIdsView->sheetIds,
            $locale
        );

        foreach ($participants as $participant) {
            $rawSheets[] = $this->getParticipantRawData($participant, $locale);
        }

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
        return $data instanceof ParticipantsSheetIdsView && 'csv' === $format;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return array Raw data about sheet
     */
    private function getParticipantRawData(Participant $participant, string $locale)
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

        // 1. Common fields (sheet ID, participant ID, etc.)
        $rawData = [
            self::COL_SHEET_ID               => $sheet->getId(),
            self::COL_PARTICIPANT_TYPE       => $sheet->getType()->getTitle($availableLocale),
            self::COL_SHEET_NAME             => $sheet->getTitle(),
            self::COL_SHEET_ENABLE           => $this->normalizeBoolean($sheet->isEnabled()),
            self::COL_USER_ID                => $participant->getUser()->getId(),
            self::COL_PARTICIPANT_ID         => $participant->getId(),
            self::COL_PARTICIPANT_EMAIL      => $participant->getUser()->getEmail(),
            self::COL_PARTICIPANT_CREATED_AT => $timeFormatter->format($sheet->getCreatedAt()),
            self::COL_HAPPENING_SUBSCRIBER   => $this->getHappeningSubscriberData($participant),
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
    private function addRegistrationRawData(&$rawData, Participant $participant, $availableLocale, $fallbackLocale): void
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
    private function normalizeParticipantRawData($rawData, $charset = Charset::WINDOWS_1252): array
    {
        $normalizedData = [];

        // Common fields (event ID, event name, etc.)
        foreach (self::COMMON_COL as $fieldKey) {
            $translationKey = sprintf('%s%s', self::TRANSLATION_KEY, $fieldKey);
            $input          = $rawData[$fieldKey];

            $translatedFieldName = $this->convertCharset(
                $this->translator->trans($translationKey),
                Charset::UTF_8,
                $charset
            );

            $normalizedData[$translatedFieldName] = $this->convertCharset(
                $input,
                Charset::UTF_8,
                $charset
            );
        }

        // Registration data
        foreach ($this->registrationFields as $fieldKey => $fieldName) {
            $input     = isset($rawData[$fieldKey]) ? $rawData[$fieldKey] : null;
            $fieldName = $this->convertCharset($fieldName, Charset::UTF_8, $charset);

            // Avoid set to null in field with same name
            if (null === $input && isset($normalizedData[$fieldName])) {
                continue;
            }

            $normalizedData[$fieldName] = $this->normalizeInput($input, Charset::UTF_8, $charset);
        }

        return $normalizedData;
    }

    /**
     * @param Participant $participant
     *
     * @return string
     */
    private function getHappeningSubscriberData(Participant $participant): string
    {
        $transKeyHappeningSubscriber = 'admin.participant.export.happening.subscriber.';

        $happeningSubscriber = $this
            ->happeningParticipationRepository
            ->checkAnyParticipation(
                $participant->getUser(),
                $participant->getSheet()->getEvent()
            ) ? 'yes' : 'no';

        return $this->translator->trans($transKeyHappeningSubscriber . $happeningSubscriber);
    }
}
