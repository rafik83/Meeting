<?php

namespace Proximum\Vimeet\Infrastructure\Elastica\Transformer;

use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Order\SheetOrderStatus;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateBooleanFilterIdentifier;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateFilledFilter;
use Proximum\Vimeet\Domain\Template\TemplateObject\IndexableObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Template\TemplateObject\SearchableObjectInterface;
use Proximum\Vimeet\Infrastructure\Elastica\AvailableLocales;
use Proximum\Vimeet\Infrastructure\Elastica\SheetContentView;
use Symfony\Component\Intl\Intl;

class SheetElasticTransformer implements ModelToElasticaTransformerInterface
{
    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var CartRowRepositoryInterface */
    private $cartRowRepository;

    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var RequestRepositoryInterface */
    private $meetingRequestRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var Balance */
    private $orderBalance;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var InvoiceRepositoryInterface */
    private $invoiceRepository;

    /** @var TaggedDataFactory */
    private $taggedDataFactory;

    /** @var SheetOrderStatus */
    private $sheetOrderStatus;

    /** @var CampaignRepositoryInterface */
    private $campaignRepository;

    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        CartRowRepositoryInterface $cartRowRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        RequestRepositoryInterface $meetingRequestRepository,
        TemplateDataFactory $templateDataFactory,
        TaggedDataFactory $taggedDataFactory,
        Balance $orderBalance,
        MeetingRepositoryInterface $meetingRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        SheetOrderStatus $sheetOrderStatus,
        CampaignRepositoryInterface $campaignRepository
    ) {
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->cartRowRepository = $cartRowRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->taggedDataFactory = $taggedDataFactory;
        $this->orderBalance = $orderBalance;
        $this->meetingRepository = $meetingRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->meetingRequestRepository = $meetingRequestRepository;
        $this->sheetOrderStatus = $sheetOrderStatus;
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @param Sheet $sheet
     * @param array $fields
     *
     * @return Document
     */
    public function transform($sheet, array $fields)
    {
        $fallbackLocale = $sheet->getEvent()->getFallback();
        $registrationTemplateData = $this->templateDataFactory->createRegistrationFromSheet($sheet, $fallbackLocale);
        $sheetTemplateData = $this->templateDataFactory->createFromSheet($sheet, $fallbackLocale);
        $sheetContentView = $this->getSheetContentView($sheet);

        return new Document(
            $sheet->getId(),
            array_merge(
                [
                    'id' => $sheet->getId(),
                    'sheetName' => $this->sheetInfoGuesser->guessSheetTitle($sheet, $fallbackLocale),
                    'state' => $sheet->getState(),
                    'validationState' => $sheet->getValidationState(),
                    'agendaConfirmedStatus' => $sheet->getAgendaConfirmedStatus(),
                    'phoneValidationStatus' => $sheet->getPhoneValidationStatus(),
                    'availabilityConfirmationStatus' => $sheet->getAvailabilityConfirmationStatus(),
                    'enabled' => $sheet->isEnabled(),
                    'completed' => $sheet->isCompleted(),
                    'completeness' => $sheet->getCompleteness(),
                    'type' => $sheet->getType()->getId(),
                    'categories' => $this->buildCategories($sheet),
                    'followUp' => $this->getFollowerId($sheet),
                    'commercialStatus' => $sheet->getCommercialStatus(),
                    'participantNumber' => $sheet->countParticipants(),
                    'participants' => $this->getParticipantsData($sheet, $fallbackLocale),
                    'event' => $sheet->getEvent()->getId(),
                    'owner' => $sheet->getOwner()->getId(),
                    'ownerEmail' => $sheet->getOwner()->getEmail(),
                    'remainingToPay' => $this->orderBalance->getRemainingToPay($sheet),
                    'imported' => $sheet->isImported(),
                    'lastLoginAt' => $sheet->getLastLoginAt() ? $sheet->getLastLoginAt()->format('c') : null,
                    'createdAt' => $sheet->getCreatedAt()->format('c'),
                    'inCatalog' => $sheet->isInInternalCatalog(),
                    'inCatalogAt' => null !== $sheet->getInCatalogAt() ? $sheet->getInCatalogAt()->format('c') : null,
                    'booleanFilter' => TemplateBooleanFilterIdentifier::getBooleanFilterValues($registrationTemplateData),
                    'filledFilter' => TemplateFilledFilter::getFilledFilterValues($registrationTemplateData, $sheet),
                    'orderStatus' => $this->sheetOrderStatus->getStatus($sheet),
                    'hasCart' => $this->hasCart($sheet),
                    'organizationCategory' => $this->getOrganizationCategory($registrationTemplateData),
                    'content' => implode(' ', $sheetContentView->content),
                    'city' => $this->getCity($registrationTemplateData),
                    'zipcode' => $this->getZipcode($registrationTemplateData),
                    'country' => $this->buildCountries($registrationTemplateData, $sheet->getEvent()->getLocales()),
                    'countryCode' => $this->getCountryCode($registrationTemplateData),
                    'nomenclatureItems' => $this->buildNomenclatureItems($sheet, $sheetTemplateData),
                    'nomenclatureItemsSupply' => $this
                        ->buildNomenclatureItems($sheet, $sheetTemplateData, Nomenclature::OBJECTIVE_SUPPLY),
                    'nomenclatureItemsNeeds' => $this
                        ->buildNomenclatureItems($sheet, $sheetTemplateData, Nomenclature::OBJECTIVE_NEED),
                    'keywords' => $this->buildKeywords($sheet),
                    'hasHappeningParticipation' => $this->happeningParticipationRepository
                        ->hasParticipationsBySheet($sheet),
                    'hasMeetingRequest' => $this->meetingRequestRepository->hasRequestSentBySheet($sheet),
                    'hasPendingMeetingProposition' => $this->meetingRequestRepository
                        ->hasPendingPropositionReceivedBySheet($sheet),
                    'hasScheduledMeeting' => $this->meetingRepository->hasScheduledMeeting($sheet),
                    'hasInvoice' => $this->invoiceRepository->hasInvoice($sheet),
                    'attend' => $sheet->attend(),
                    'hasGroup' => $sheet->hasGroup(),
                    'spotReference' => $sheet->hasSpot() ? $sheet->getSpot()->getReference() : null,
                    'hasSpot' => $sheet->hasSpot(),
                    'availableSlotIds' => $this->buildAvailableSlots($sheet),
                    'reminderDate' => $this->getReminderDate($sheet),
                    'nestedTaggedData' => $this->getNestedTaggedData($registrationTemplateData, $sheetTemplateData),
                    'messagesReceived' => $this->campaignRepository->getBySheet($sheet)
                ],
                $sheetContentView->contentByLocale
            )
        );
    }

    private function getFollowerId(Sheet $sheet): ?int
    {
        return $sheet->getFollower() instanceof Admin ? $sheet->getFollower()->getId() : null;
    }

    private function getParticipantsData(Sheet $sheet, string $fallbackLocale): array
    {
        $participants = $this->buildParticipants($sheet, $fallbackLocale);

        if (!$sheet->hasUserParticipant($sheet->getOwner())) {
            $participants[] = [
                'email' => $sheet->getOwner()->getEmail(),
                'lastname' => $sheet->getOwner()->getAccount()->getLastName(),
            ];
        }

        return $participants;
    }

    private function hasCart(Sheet $sheet): bool
    {
        return \count($this->cartRowRepository->findBySheet($sheet)) > 0;
    }

    private function getReminderDate(Sheet $sheet): ?string
    {
        return null !== $sheet->getReminderDate() ? $sheet->getReminderDate()->format('c') : null;
    }

    private function getOrganizationCategory(TemplateData $registrationTemplateData)
    {
        $organizationCategory = $registrationTemplateData->getTaggedContentValue(Tag::SHEET_ORGANIZATION_CATEGORY);

        return \in_array($organizationCategory, [false, ''], true) ? null : $organizationCategory;
    }

    private function getSheetContentView(Sheet $sheet): SheetContentView
    {
        $content = [];
        $contentByLocale = [];

        foreach ($sheet->getEvent()->getLocales() as $locale) {
            $templateData = $this->taggedDataFactory->buildTaggedDataView($sheet, $locale);
            $localeContent = $this->getSearchableContent($templateData->getObjects(), $locale);

            // Add locale content in same field
            $content[] = $localeContent;

            if (\in_array($locale, AvailableLocales::getAvailableLocalesForContent(), true)) {
                $contentByLocale[sprintf('content_%s', $locale)] = $localeContent;
            }
        }

        return new SheetContentView($contentByLocale, $content);
    }

    private function getSearchableContent(array $templatesObjects, string $locale): string
    {
        $searchableContent = [];

        foreach ($templatesObjects as $templateObject) {
            if ($templateObject instanceof SearchableObjectInterface) {
                $content = $templateObject->getSearchableContent($locale);

                if (\is_array($content)) {
                    foreach ($content as $item) {
                        $searchableContent[] = $item;
                    }
                } elseif (null !== $content && !empty($content)) {
                    $searchableContent[] = $content;
                }
            }
        }

        return implode(' ', $searchableContent);
    }

    private function getCountryCode(TemplateData $templateData): ?string
    {
        $countryCode = $templateData->getTaggedContentValue(Tag::SHEET_COUNTRY);

        if (false === $countryCode) {
            return null;
        }

        return $countryCode;
    }

    private function buildCountries(TemplateData $templateData, array $locales): array
    {
        $locales = array_values($locales);
        $countryCode = $this->getCountryCode($templateData);

        if (null === $countryCode) {
            return [];
        }

        $countries = [];
        $regionBundle = Intl::getRegionBundle();

        foreach ($locales as $key => $locale) {
            $countryName = $regionBundle->getCountryName($countryCode, $locale);

            $countries[$key]['locale'] = $locale;
            $countries[$key]['label'] = $countryName;
            $countries[$key]['label_autocomplete'] = $countryName;
        }

        return $countries;
    }

    private function buildKeywords(Sheet $sheet): array
    {
        if (!$sheet->isInExternalOrInternalCatalog()) {
            return [];
        }

        $keywords = [];
        $keywordIndex = 0;

        foreach ($sheet->getEvent()->getLocales() as $locale) {
            $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);

            foreach ($templateData->getObjects() as $templateObject) {
                if ($templateObject instanceof IndexableObjectInterface) {
                    $content = $templateObject->getSearchableContent();

                    if (\is_array($content)) {
                        foreach ($content as $item) {
                            $keywords[$keywordIndex]['label'] = $item;
                            $keywords[$keywordIndex]['label_autocomplete'] = $item;
                            $keywords[$keywordIndex]['locale'] = $locale;
                            ++$keywordIndex;
                        }
                    } elseif (null !== $content && !empty($content)) {
                        $keywords[$keywordIndex]['label'] = $content;
                        $keywords[$keywordIndex]['label_autocomplete'] = $content;
                        $keywords[$keywordIndex]['locale'] = $locale;
                        ++$keywordIndex;
                    }
                }
            }
        }

        return $keywords;
    }

    private function getZipcode(TemplateData $templateData): ?string
    {
        $countryCode = $this->getCountryCode($templateData);

        if (!in_array($countryCode, ['FR', null], true)) {
            return null;
        }

        $zipcode = $templateData->getTaggedContentValue(Tag::SHEET_ZIPCODE);

        if (!$zipcode) {
            return null;
        }

        $zipcode = substr(str_replace(' ', '', $zipcode), 0, 5);

        if (4 === mb_strlen($zipcode)) {
            return '0' . $zipcode[0];
        }

        if (5 === mb_strlen($zipcode)) {
            return substr($zipcode, 0, 2);
        }

        return null;
    }

    private function getCity(TemplateData $templateData): ?string
    {
        return $templateData->getTaggedContentValue(Tag::SHEET_CITY) ?: null;
    }

    private function buildParticipants(Sheet $sheet, string $locale): array
    {
        $participants = array_map(
            function (Participant $participant) use ($locale) {
                return [
                    'email' => $participant->getUser()->getEmail(),
                    'lastname' => $this->participantInfoGuesser->guessParticipantLastName(
                        $participant,
                        $locale
                    ),
                    'position' => $this->participantInfoGuesser->guessParticipantPosition(
                        $participant,
                        $locale
                    ),
                ];
            },
            $sheet->getParticipantsArray()
        );

        return $participants;
    }

    /**
     * @return int[] Category ids
     */
    private function buildCategories(Sheet $sheet): array
    {
        $categories = array_map(
            function (Category $category) {
                return ['id' => $category->getId()];
            },
            $sheet->getType()->getCategories()->toArray()
        );

        return $categories;
    }

    private function buildNomenclatureItems(
        Sheet $sheet,
        TemplateData $data,
        string $objective = Nomenclature::OBJECTIVE_NONE
    ): array {
        if (!$sheet->isInExternalOrInternalCatalog()) {
            return [];
        }

        $nomenclatureItems = [];
        $nomenclatureObjects = $data->getNomenclatureObjectsByObjective($objective);

        foreach ($nomenclatureObjects as $nomenclatureObject) {
            $items = $nomenclatureObject->getData();

            if (isset($items['items'])) {
                foreach ($items['items'] as $item) {
                    $nomenclatureItems[]['key'] = mb_strtolower($item);
                }
            }
        }

        return $nomenclatureItems;
    }

    /**
     * @return int[] AvailableSlot ids
     */
    private function buildAvailableSlots(Sheet $sheet): array
    {
        if (!$sheet->isInInternalCatalog()) {
            return [];
        }

        $ids = array_map(
            function (Sheet\AvailableSlot $availableSlot) {
                return ['id' => $availableSlot->getSlot()->getId()];
            },
            $sheet->getAvailableSlots()
        );

        return $ids;
    }

    private function getNestedTaggedDataFromTemplateData(TemplateData $templateData): array
    {
        $nestedTaggedData = [];

        foreach ($templateData->getObjects() as $object) {
            if ($object instanceof Nomenclature) {
                foreach ($object->getTags() as $tag) {
                    if (!\in_array($tag, Tag::getSheetAndGenericSheetTagsAndGenericSheetTemplateTags(), true)) {
                        continue;
                    }

                    $items = $object->getItems();

                    if (empty($items)) {
                        continue;
                    }

                    if (isset($nestedTaggedData[$tag])) {
                        $nestedTaggedData[$tag]['values'] = array_merge(
                            $nestedTaggedData[$tag]['values'],
                            array_map(function ($item) use ($tag) {
                                return [
                                    'tag' => mb_strtolower($tag),
                                    'value' => mb_strtolower($item),
                                ];
                            }, $items)
                        );

                        continue;
                    }

                    $nestedTaggedData[$tag] = [
                        'tag' => $tag,
                        'values' => array_map(function ($item) use ($tag) {
                            return [
                                'tag' => mb_strtolower($tag),
                                'value' => mb_strtolower($item),
                            ];
                        }, $items),
                    ];
                }
            }
        }

        return $nestedTaggedData;
    }

    private function getNestedTaggedData(TemplateData $registrationTemplateData, TemplateData $sheetTemplateData): array
    {
        return array_values(
            array_merge(
                $this->getNestedTaggedDataFromTemplateData($registrationTemplateData),
                $this->getNestedTaggedDataFromTemplateData($sheetTemplateData)
            )
        );
    }
}
