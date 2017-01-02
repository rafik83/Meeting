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
use Proximum\Vimeet\Application\Nomenclature\Charset;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ExportableObjectInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Event sheets normalizer meant to be used as part of {Symfony's Serializer component @link
 * http://symfony.com/doc/current/serializer.html}.
 *
 * Normalized sheets data are dispatched into three field groups:
 *
 * - common fields ({@link self::getCommonFieldKeys})
 * - registration fields ({@link self::$registrationFields})
 * - sheet fields ({@link self::$sheetFields})
 */
class EventSheetsNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_EVENT_ID          = 'event_id';
    const COL_EVENT_NAME        = 'event_name';
    const COL_SHEET_ID          = 'sheet_id';
    const COL_OWNER_ID          = 'owner_id';
    const COL_OWNER_EMAIL       = 'owner_email';
    const COL_TYPE              = 'type';
    const COL_CATEGORY          = 'category';
    const COL_REGISTRATION_DATE = 'registration_date';
    const COL_PARTICIPANTS      = 'participants';
    const COL_STATUS            = 'status';
    const COL_FOLLOWING         = 'following';
    const COL_IN_CATALOG        = 'in_catalog';

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

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
     * @param SheetRepositoryInterface $sheetRepository
     * @param TemplateDataFactory      $templateDataFactory
     * @param TranslatorInterface      $translator
     */
    public function __construct(
        TranslatorInterface $translator,
        SheetRepositoryInterface $sheetRepository,
        TemplateDataFactory $templateDataFactory
    ) {
        parent::__construct($translator);

        $this->sheetRepository     = $sheetRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->sheetFields         = [];
        $this->registrationFields  = [];
    }

    /**
     * Normalizes an event's sheets for serialization
     *
     * {@inheritdoc}
     *
     * @param Event $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $rawSheets = [];
        $locale    = $context['locale'];

        foreach ($this->sheetRepository->getEnabledSheetsByEvent($object) as $sheet) {
            $rawSheets[] = $this->getSheetRawData($sheet, $locale);
        }

        $charset          = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;
        $normalizedSheets = [];
        foreach ($rawSheets as $rawSheet) {
            $normalizedSheets[] = $this->normalizeSheetRawData($rawSheet, $charset);
        }

        return $normalizedSheets;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Event && 'csv' === $format;
    }

    /**
     * Return an array of raw data for a given Sheet and locale ('raw' meaning unescaped content, untranslated column
     * names, not human-readable column names for dynamic columns, etc.)
     *
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return array Raw data about sheet
     */
    private function getSheetRawData(Sheet $sheet, $locale)
    {
        $event    = $sheet->getEvent();
        $owner    = $sheet->getOwner();
        $follower = $sheet->getFollower();

        $availableLocale = $event->getAvailableLocale($locale);
        $fallbackLocale  = $event->getFallback();

        $categories = implode(';', array_map(
            function (Category $category) use ($availableLocale) {
                return str_replace(';', ',', $category->getTitle($availableLocale));
            },
            $sheet->getType()->getCategories()->toArray()
        ));

        // 1. Common fields (event ID, event name, etc.)
        $rawData = [
            self::COL_EVENT_ID          => $event->getId(),
            self::COL_EVENT_NAME        => $event->getTitle(),
            self::COL_SHEET_ID          => $sheet->getId(),
            self::COL_OWNER_ID          => $owner->getId(),
            self::COL_OWNER_EMAIL       => $owner->getEmail(),
            self::COL_TYPE              => $sheet->getType()->getTitle($availableLocale),
            self::COL_CATEGORY          => $categories,
            self::COL_REGISTRATION_DATE => $sheet->getCreatedAt()->format('d/m/Y'),
            self::COL_PARTICIPANTS      => $sheet->countParticipant(),
            self::COL_STATUS            => $sheet->getValidationState(),
            self::COL_FOLLOWING         => null !== $follower ? $follower->getDisplayName() : '',
            self::COL_IN_CATALOG        => $this->normalizeBoolean($sheet->isInCatalog()),
        ];

        // 2. Registration data
        $this->addRegistrationRawData($rawData, $sheet, $availableLocale, $fallbackLocale);

        // 3. Sheet presentation data
        $this->addPresentationRawData($rawData, $sheet, $availableLocale, $fallbackLocale);

        return $rawData;
    }

    /**
     * @param array  $rawData
     * @param Sheet  $sheet
     * @param string $availableLocale
     * @param string $fallbackLocale
     */
    private function addPresentationRawData(&$rawData, Sheet $sheet, $availableLocale, $fallbackLocale)
    {
        $presentationTemplateData = $this->templateDataFactory->createFromSheet($sheet, $availableLocale);
        foreach ($presentationTemplateData->getObjects() as $presentationObject) {
            if ($presentationObject instanceof ExportableObjectInterface) {
                $key = $presentationObject->getKey();
                if (!isset($this->sheetFields[$key])) {
                    $fieldName = $presentationObject->getExportableFieldname($availableLocale, $fallbackLocale);
                    $this->sheetFields[$key] = $fieldName;
                }
                $rawData[$key] = $presentationObject->getExportableContent();
            }
        }
    }

    /**
     * @param array  $rawData
     * @param Sheet  $sheet
     * @param string $availableLocale
     * @param string $fallbackLocale
     */
    private function addRegistrationRawData(&$rawData, Sheet $sheet, $availableLocale, $fallbackLocale)
    {
        $registrationTemplateData = $this->templateDataFactory->createRegistrationFromSheet($sheet, $availableLocale);
        foreach ($registrationTemplateData->getEditableSheetDataExceptedImageObjects() as $registrationObject) {
            if ($registrationObject instanceof ExportableObjectInterface) {
                $key = $registrationObject->getKey();
                if (!isset($this->registrationFields[$key])) {
                    $fieldName = $registrationObject->getExportableFieldname($availableLocale, $fallbackLocale);
                    $this->registrationFields[$key] = $fieldName;
                }
                $rawData[$key] = $registrationObject->getExportableContent();
            }
        }
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
    private function normalizeSheetRawData($rawData, $charset = Charset::WINDOWS_1252)
    {
        $normalizedData = [];

        // Common fields (event ID, event name, etc.)
        foreach (self::getCommonFieldKeys() as $fieldKey) {
            $translationKey = 'admin.sheet.export.fields.' . $fieldKey;
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

        // Sheet data
        foreach ($this->sheetFields as $fieldKey => $fieldName) {
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
            self::COL_EVENT_ID,
            self::COL_EVENT_NAME,
            self::COL_SHEET_ID,
            self::COL_OWNER_ID,
            self::COL_OWNER_EMAIL,
            self::COL_TYPE,
            self::COL_CATEGORY,
            self::COL_REGISTRATION_DATE,
            self::COL_PARTICIPANTS,
            self::COL_STATUS,
            self::COL_FOLLOWING,
            self::COL_IN_CATALOG,
        ];
    }
}
