<?php

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Sheet\Detail\CRM\RecordViewsQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\CRM\RecordViewsQueryHandler;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Sheet\Details\CRM\RecordView;
use Proximum\Vimeet\Application\View\Sheet\SheetIdsView;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Money\AmountFormatter;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ExportableObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;
use Proximum\Vimeet\Domain\Template\TemplateObject\MediaCollection;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Sheet list views normalizer meant to be used as part of {Symfony's Serializer component @link
 * http://symfony.com/doc/current/serializer.html}.
 *
 * Normalized sheets data are dispatched into three field groups:
 *
 * - common fields ({@link self::getCommonFieldKeys})
 * - registration fields ({@link self::$registrationFields})
 * - sheet fields ({@link self::$sheetFields})
 */
class SheetIdsViewNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    public const TRANSLATION_COL = 'admin.sheet.export.fields';
    public const COL_EVENT_ID = 'event_id';
    public const COL_EVENT_NAME = 'event_name';
    public const COL_SHEET_ID = 'sheet_id';
    public const COL_SHEET_ENABLE = 'sheet_enable';
    public const COL_OWNER_ID = 'owner_id';
    public const COL_OWNER_FIRSTNAME = 'owner_first_name';
    public const COL_OWNER_LASTNAME = 'owner_last_name';
    public const COL_OWNER_EMAIL = 'owner_email';
    public const COL_OWNER_PHONE = 'owner_phone';
    public const COL_OWNER_MOBILE = 'owner_mobile';
    public const COL_TYPE = 'type';
    public const COL_CATEGORY = 'category';
    public const COL_REGISTRATION_DATE = 'registration_date';
    public const COL_PARTICIPANTS = 'participants';
    public const COL_STATUS = 'status'; // Validation State
    public const COL_FOLLOWING = 'following';  // Admin that follow up the sheet
    public const COL_IN_CATALOG = 'in_catalog';
    public const COL_ORDER_PROMO_CODE = 'order_promo_code';
    public const COL_SHEET_STATE = 'sheet_state'; // State
    public const COL_TOTAL_ORDER = 'total_excluded_vat'; // Total hors taxes
    public const COL_BALANCE = 'balance';
    public const COL_COMMENTS = 'comments';
    public const COL_COMMERCIAL_STATUS = 'commercial_status';
    public const COL_SPOT = 'sheet_spot';

    public const TRANSLATION_KEY_COMMERCIAL_STATUS = 'admin.sheet.details.crm.record.trace.set_commercial_status.';
    public const TRANSLATION_KEY_COMMENT = 'admin.sheet.export.field.comment';

    public const COMMON_COL = [
        self::COL_EVENT_ID,
        self::COL_EVENT_NAME,
        self::COL_SHEET_ID,
        self::COL_SHEET_ENABLE,
        self::COL_SHEET_STATE,
        self::COL_SPOT,
        self::COL_OWNER_ID,
        self::COL_OWNER_FIRSTNAME,
        self::COL_OWNER_LASTNAME,
        self::COL_OWNER_EMAIL,
        self::COL_OWNER_PHONE,
        self::COL_OWNER_MOBILE,
        self::COL_TYPE,
        self::COL_CATEGORY,
        self::COL_REGISTRATION_DATE,
        self::COL_PARTICIPANTS,
        self::COL_STATUS,
        self::COL_FOLLOWING,
        self::COL_IN_CATALOG,
        self::COL_ORDER_PROMO_CODE,
        self::COL_TOTAL_ORDER,
        self::COL_BALANCE,
        self::COL_COMMENTS,
        self::COL_COMMERCIAL_STATUS,
    ];

    protected $normalizerType = 'sheet';

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * List of all sheet fields (merging of the sheet data fields of all normalized sheets)
     *
     * @var array (key => label)
     */
    private $sheetFields;

    /**
     * List of all registration fields (merging of the registration data fields of all normalized sheets)
     *
     * @var array (key => label)
     */
    private $registrationFields;

    /**
     * Order merger
     *
     * @var Merger
     */
    private $merger;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * RecordViewsQueryHandler is used to get the comment and commercialStatus historic
     *
     * @var RecordViewsQueryHandler
     */
    private $recordViewsQueryHandler;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var string[] indexed by type id */
    private $typeTitles = [];

    /** @var string[] indexed by type id */
    private $categories = [];

    public function __construct(
        TranslatorInterface $translator,
        SheetRepositoryInterface $sheetRepository,
        TemplateDataFactory $templateDataFactory,
        OrderRepositoryInterface $orderRepository,
        Merger $merger,
        Balance $balance,
        RecordViewsQueryHandler $recordViewsQueryHandler,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        parent::__construct($translator);

        $this->sheetRepository = $sheetRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->sheetFields = [];
        $this->registrationFields = [];
        $this->merger = $merger;
        $this->balance = $balance;
        $this->orderRepository = $orderRepository;
        $this->recordViewsQueryHandler = $recordViewsQueryHandler;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    /**
     * Normalizes an event's sheets for serialization
     *
     * {@inheritdoc}
     *
     * @param SheetIdsView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $sheetIds = $object->sheetIds;
        $event = $context['event'];
        $locale = $context['locale'];
        $charset = $context['charset'];

        $availableLocale = $event->getAvailableLocale($locale);
        $fallbackLocale = $event->getLocaleFallback();
        $eventUrl = $this->eventUrlGenerator->generateBaseEventAbsoluteUrl($event);

        // Preload transaction and order to avoid a query by sheet
        $this->balance->loadAllForSheetIds($event, $sheetIds);

        $sheets = $this->sheetRepository->getSheetsByEventAndIds($event, $sheetIds);

        $rawSheets = [];

        foreach ($sheets as $sheet) {
            $rawSheets[] = $this->getSheetRawData(
                $event,
                $sheet,
                $availableLocale,
                $fallbackLocale,
                $eventUrl,
                $context
            );
        }

        unset($sheets, $event);

        $normalizedSheets = [];

        foreach ($rawSheets as $rawSheet) {
            $normalizedSheets[] = $this->normalizeSheetRawData($rawSheet, $charset);
        }

        return $normalizedSheets;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof SheetIdsView && 'csv' === $format;
    }

    /**
     * Return an array of raw data for a given Sheet and locale ('raw' meaning unescaped content, untranslated column
     * names, not human-readable column names for dynamic columns, etc.)
     *
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     * @param string $fallbackLocale
     * @param string $eventUrl
     * @param array  $context
     *
     * @return array Raw data about sheet
     */
    private function getSheetRawData(
        Event $event,
        Sheet $sheet,
        string $locale,
        string $fallbackLocale,
        string $eventUrl,
        array $context
    ): array {
        $owner = $sheet->getOwner();
        $follower = $sheet->getFollower();

        $promotionCodes = [];
        $notCancelledOrders = $this->orderRepository->findNotCancelledBySheet($sheet);

        if (!empty($notCancelledOrders)) {
            $order = $this->merger->merge($notCancelledOrders);

            foreach ($order->getPromotionCodes() as $orderPromotionCode) {
                $promotionCodes[] = $orderPromotionCode->getPromotionCode()->getCode();
            }
        }

        $totalWithoutVat = AmountFormatter::centsToDecimalAmount($this->balance->getTotalWithoutVat($sheet));
        $balance = AmountFormatter::centsToDecimalAmount($this->balance->getBalance($sheet));

        // 1. Common fields (event ID, event name, etc.)
        $rawData = [
            self::COL_EVENT_ID          => $event->getId(),
            self::COL_EVENT_NAME        => $event->getTitle(),
            self::COL_SHEET_ID          => $sheet->getId(),
            self::COL_SHEET_ENABLE      => $this->normalizeBoolean($sheet->isEnabled()),
            self::COL_SHEET_STATE       => $sheet->getState(),
            self::COL_SPOT              => $sheet->getSpot() instanceof Spot ? $sheet->getSpot()->getReference() : null,
            self::COL_OWNER_ID          => $owner->getId(),
            self::COL_OWNER_FIRSTNAME   => $owner->getFirstName(),
            self::COL_OWNER_LASTNAME    => $owner->getLastName(),
            self::COL_OWNER_EMAIL       => $owner->getEmail(),
            self::COL_OWNER_PHONE       => $owner->getPhone(),
            self::COL_OWNER_MOBILE      => $owner->getMobile(),
            self::COL_TYPE              => $this->getTypeTitle($sheet, $locale),
            self::COL_CATEGORY          => $this->getCategories($sheet, $locale),
            self::COL_REGISTRATION_DATE => $sheet->getCreatedAt()->format('d/m/Y'),
            self::COL_PARTICIPANTS      => $sheet->countParticipants(),
            self::COL_STATUS            => $sheet->getValidationState(),
            self::COL_FOLLOWING         => null !== $follower ? $follower->getDisplayName() : '',
            self::COL_IN_CATALOG        => $this->normalizeBoolean($sheet->isInInternalCatalog()),
            self::COL_ORDER_PROMO_CODE  => implode(',', $promotionCodes),
            self::COL_TOTAL_ORDER       => $totalWithoutVat,
            self::COL_BALANCE           => $balance,
            self::COL_COMMENTS          => $this->getCommentHistoric($sheet, $locale),
            self::COL_COMMERCIAL_STATUS => $this->getCommercialStatus($sheet),
        ];

        // 2. Registration data
        $this->addRegistrationRawData($rawData, $sheet, $locale, $fallbackLocale, $context);

        // 3. Sheet presentation data
        $this->addPresentationRawData($rawData, $sheet, $locale, $fallbackLocale, $eventUrl, $context);

        return $rawData;
    }

    private function getTypeTitle(Sheet $sheet, string $locale): string
    {
        $typeId = $sheet->getType()->getId();

        if (isset($this->typeTitles[$typeId])) {
            return $this->typeTitles[$typeId];
        }

        $this->typeTitles[$typeId] = $sheet->getType()->getTitle($locale);

        return $this->typeTitles[$typeId];
    }

    private function getCategories(Sheet $sheet, string $locale): string
    {
        $typeId = $sheet->getType()->getId();

        if (isset($this->categories[$typeId])) {
            return $this->categories[$typeId];
        }

        $categories = implode(';', array_map(
            static function (Category $category) use ($locale) {
                return str_replace(';', ',', $category->getTitle($locale));
            },
            $sheet->getType()->getCategories()->toArray()
        ));

        $this->categories[$typeId] = $categories;

        return $this->categories[$typeId];
    }

    private function getCommercialStatus(Sheet $sheet): string
    {
        return null !== $sheet->getCommercialStatus()
            ? $this->translator->trans(sprintf('%s%s', self::TRANSLATION_KEY_COMMERCIAL_STATUS, $sheet->getCommercialStatus()))
            : ''
        ;
    }

    private function getCommentHistoric(Sheet $sheet, string $locale): string
    {
        return implode("\r\n", array_map(function (RecordView $recordView) use ($locale) {
            return $this->translator->trans(
                self::TRANSLATION_KEY_COMMENT,
                [
                    '%author%' => $recordView->author->getDisplayName(),
                    '%date%' => $recordView->createdAt->format('d/m/Y H:i'),
                    '%comment%' => $recordView->isComment()
                        ? $recordView->comment
                        : $this->translator->trans($recordView->getTraceTranslationKey() . $recordView->comment),
                ],
                'messages',
                $locale
            );
        }, $this->recordViewsQueryHandler->handle(new RecordViewsQuery($sheet))));
    }

    /**
     * This method formats all sheet_template fields
     *
     * @param array  $rawData
     * @param Sheet  $sheet
     * @param string $availableLocale
     * @param string $fallbackLocale
     * @param array  $context
     * @param string $eventUrl
     */
    private function addPresentationRawData(
        &$rawData,
        Sheet $sheet,
        string $availableLocale,
        string $fallbackLocale,
        string $eventUrl,
        array $context = []
    ): void {
        $presentationTemplateData = $this->templateDataFactory->createFromSheet($sheet, $availableLocale);

        // the tagged data are used in case of empty field
        $taggedData = $this->templateDataFactory->createRegistrationFromSheet($sheet, $availableLocale)->getAllTaggedDatas();

        $sheetId = $sheet->getId();
        $context = array_merge($context, ['taggedData' => $taggedData]);

        /** @var ExportableObjectInterface|MultiUploadCollectionObject|MediaCollection $presentationObject */
        foreach ($presentationTemplateData->getExportableObjectsWithMediaAndUpload() as $presentationObject) {
            $data = null;
            $key = $presentationObject->getKey();

            if (!isset($this->sheetFields[$key])) {
                $fieldName = $presentationObject->getExportableFieldname($availableLocale, $fallbackLocale);
                $this->sheetFields[$key] = $fieldName;
            }

            if ($presentationObject instanceof ExportableObjectInterface) {
                $data = $this->getExportableContent($presentationObject, $context);

                if($presentationObject instanceof Image && $data !== '') {
                    $data = $eventUrl.$data;
                }
            } elseif ($presentationObject instanceof MediaCollection) {
                $data = $presentationObject->getExportableContent();
            } elseif ($presentationObject instanceof MultiUploadCollectionObject) {
                $data = $presentationObject->getExportableUploads($eventUrl, $sheetId, $fallbackLocale);
            }

            $rawData[$key] = $data;
        }

        unset($presentationTemplateData, $taggedData);
    }

    /**
     * This method formats all registration fields with the tag SHEET_DATA
     *
     * @param array  $rawData
     * @param Sheet  $sheet
     * @param string $availableLocale
     * @param string $fallbackLocale
     * @param array  $context
     */
    private function addRegistrationRawData(
        &$rawData,
        Sheet $sheet,
        string $availableLocale,
        string $fallbackLocale,
        array $context = []
    ): void {
        $registrationTemplateData = $this->templateDataFactory->createRegistrationFromSheet($sheet, $availableLocale);

        foreach ($registrationTemplateData->getEditableSheetDataExceptedImageObjects() as $registrationObject) {
            if ($registrationObject instanceof ExportableObjectInterface) {
                $key = $registrationObject->getKey();

                if (!isset($this->registrationFields[$key])) {
                    $fieldName = $registrationObject->getExportableFieldname($availableLocale, $fallbackLocale);
                    $this->registrationFields[$key] = $fieldName;
                }

                $rawData[$key] = $this->getExportableContent($registrationObject, $context);
            }
        }

        unset($registrationTemplateData);
    }

    /**
     * @param ExportableObjectInterface $exportableObject
     * @param array                     $context
     *
     * @return string|null
     */
    private function getExportableContent(ExportableObjectInterface $exportableObject, array $context): ?string
    {
        $displayNomenclatureIds = $context['displayNomenclatureIds'] ?? false;

        if ($exportableObject instanceof Nomenclature && $displayNomenclatureIds) {
            return $exportableObject->getNomenclatureItems($displayNomenclatureIds);
        }

        return $exportableObject->getExportableContent($context['taggedData'] ?? [], $context['locale'] ?? null);
    }

    /**
     * Returns an array of normalized data from a sheet's raw data
     * (normalizing includes encoding, common field headers' translations, adding missing columns, etc.)
     *
     * @param array  $rawData
     * @param string $charset
     *
     * @return array
     */
    private function normalizeSheetRawData($rawData, $charset = Charset::WINDOWS_1252): array
    {
        $normalizedData = [];

        // Common fields (event ID, event name, etc.)
        foreach (self::COMMON_COL as $fieldKey) {
            $translationKey = sprintf('%s.%s', self::TRANSLATION_COL, $fieldKey);
            $input = $rawData[$fieldKey];

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
            $input = $rawData[$fieldKey] ?? null;

            // Avoid set to null in field with same name
            if (null === $input && isset($normalizedData[$fieldName])) {
                continue;
            }

            $fieldName = $this->convertCharset($fieldName, Charset::UTF_8, $charset);
            $normalizedData[$fieldName] = $this->normalizeInput($input, Charset::UTF_8, $charset);
        }

        // Sheet data
        foreach ($this->sheetFields as $fieldKey => $fieldName) {
            $input = $rawData[$fieldKey] ?? null;

            // Avoid set to null in field with same name
            if (null === $input && isset($normalizedData[$fieldName])) {
                continue;
            }

            $fieldName = $this->convertCharset($fieldName, Charset::UTF_8, $charset);
            $normalizedData[$fieldName] = $this->normalizeInput($input, Charset::UTF_8, $charset);
        }

        return $normalizedData;
    }
}
