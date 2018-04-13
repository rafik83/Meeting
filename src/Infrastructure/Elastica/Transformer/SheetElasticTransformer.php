<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Elastica\Transformer;

use Elastica\Document;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateBooleanFilterIdentifier;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\IndexableObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Template\TemplateObject\SearchableObjectInterface;
use Proximum\Vimeet\Infrastructure\Elastica\AvailableLocales;
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

    /**
     * @param SheetInfoGuesser                          $sheetInfoGuesser
     * @param ParticipantInfoGuesser                    $participantInfoGuesser
     * @param CartRowRepositoryInterface                $cartRowRepository
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param RequestRepositoryInterface                $meetingRequestRepository
     * @param TemplateDataFactory                       $templateDataFactory
     * @param Balance                                   $orderBalance
     * @param MeetingRepositoryInterface                $meetingRepository
     * @param InvoiceRepositoryInterface                $invoiceRepository
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        CartRowRepositoryInterface $cartRowRepository,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        RequestRepositoryInterface $meetingRequestRepository,
        TemplateDataFactory $templateDataFactory,
        Balance $orderBalance,
        MeetingRepositoryInterface $meetingRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->cartRowRepository      = $cartRowRepository;
        $this->templateDataFactory    = $templateDataFactory;
        $this->orderBalance           = $orderBalance;
        $this->meetingRepository      = $meetingRepository;
        $this->invoiceRepository      = $invoiceRepository;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->meetingRequestRepository         = $meetingRequestRepository;
    }

    /**
     * @param Sheet $sheet
     * @param array $fields
     *
     * @return Document
     */
    public function transform($sheet, array $fields)
    {
        $locale = $sheet->getEvent()->getFallback();

        $participants = [];

        if (null !== $sheet->getParticipants()) {
            $participants = $this->buildParticipants($sheet, $locale);

            if ($sheet->hasUserParticipant($sheet->getOwner())) {
                $participants[] = [
                    'email'    => $sheet->getOwner()->getEmail(),
                    'lastname' => $sheet->getOwner()->getAccount()->getLastName(),
                ];
            }
        }

        try {
            $owner      = $sheet->getOwner()->getId();
            $ownerEmail = $sheet->getOwner()->getEmail();
        } catch (\RuntimeException $e) {
            $owner      = null;
            $ownerEmail = null;
        }

        $categories = $this->buildCategories($sheet);

        $hasCart                  = count($this->cartRowRepository->findBySheet($sheet)) > 0;
        $registrationTemplateData = $this->templateDataFactory->createRegistrationFromSheet($sheet, $locale);
        $filtersValue             = TemplateBooleanFilterIdentifier::getBooleanFilterValues($registrationTemplateData);
        $organizationCategory     = $registrationTemplateData->getTaggedContentValue(Tag::SHEET_ORGANIZATION_CATEGORY);

        $content         = [];
        $contentByLocale = [];

        $fallbackLocale    = $sheet->getEvent()->getFallback();
        $fallbackData      = $this->templateDataFactory->createFromSheet($sheet, $fallbackLocale);
        $nomenclatureItems = $this->buildNomenclatureItems($fallbackData);

        foreach ($sheet->getEvent()->getLocales() as $locale) {
            if ($locale !== $fallbackLocale) {
                $data = $this->templateDataFactory->createFromSheet($sheet, $locale);
            } else {
                $data = $fallbackData;
            }

            $localeContent = $this->getSearchableContent($data->getObjects());

            // Add locale content in same field
            $content[] = $localeContent;

            // if locale field exists in ES, add it
            if (in_array($locale, AvailableLocales::getAvailableLocalesForContent())) {
                $contentByLocale[sprintf('content_%s', $locale)] = $localeContent;
            }
        }

        $countryCode = $this->getCountryCode($registrationTemplateData);

        if ($countryCode === false) {
            $countryCode = null;
        }

        return new Document($sheet->getId(), array_merge(
            [
                'id'                      => $sheet->getId(),
                'sheetName'               => $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale),
                'state'                   => $sheet->getState(),
                'validationState'         => $sheet->getValidationState(),
                'agendaConfirmedStatus'          => $sheet->getAgendaConfirmedStatus(),
                'phoneValidationStatus'          => $sheet->getPhoneValidationStatus(),
                'availabilityConfirmationStatus' => $sheet->getAvailabilityConfirmationStatus(),
                'enabled'                 => $sheet->isEnabled(),
                'completed'               => $sheet->isCompleted(),
                'completeness'            => $sheet->getCompleteness(),
                'type'                    => $sheet->getType()->getId(),
                'categories'              => $categories,
                'followUp'                => $sheet->getFollower() instanceof Admin ? $sheet->getFollower()->getId() : null,
                'commercialStatus'        => $sheet->getCommercialStatus(),
                'participantNumber'       => count($sheet->getParticipants()),
                'participants'            => $participants,
                'event'                   => $sheet->getEvent()->getId(),
                'owner'                   => $owner,
                'ownerEmail'              => $ownerEmail,
                'remainingToPay'          => $this->orderBalance->getRemainingToPay($sheet),
                'imported'                => $sheet->isImported(),
                'lastLoginAt'             => $sheet->getLastLoginAt() ? $sheet->getLastLoginAt()->format('c') : null,
                'createdAt'               => $sheet->getCreatedAt()->format('c'),
                'inCatalog'               => $sheet->isInCatalog(),
                'inCatalogAt'             => null !== $sheet->getInCatalogAt() ? $sheet->getInCatalogAt()->format('c') : null,
                'booleanFilter'           => $filtersValue,
                'orderStatus'             => $this->getOrderStatus($sheet),
                'hasCart'                 => $hasCart,
                'organizationCategory'    => in_array($organizationCategory, [false, '']) ? null : $organizationCategory,
                'content'                 => implode(' ', $content),
                'city'                    => $this->getCity($registrationTemplateData),
                'zipcode'                 => $this->getTwoFirstCharsOfFranceZipcode($registrationTemplateData),
                'country'                 => $this->buildCountry($registrationTemplateData, $sheet->getEvent()->getLocales()),
                'countryCode'             => $countryCode,
                'nomenclatureItems'       => $nomenclatureItems,
                'nomenclatureItemsSupply' => $this->buildNomenclatureItems($fallbackData, Nomenclature::OBJECTIVE_SUPPLY),
                'nomenclatureItemsNeeds'  => $this->buildNomenclatureItems($fallbackData, Nomenclature::OBJECTIVE_NEED),
                'keywords'                => $this->buildKeywords($sheet),
                'hasHappeningParticipation'    => $this->happeningParticipationRepository->hasParticipationsBySheet($sheet),
                'hasMeetingRequest'            => $this->meetingRequestRepository->hasRequestSentBySheet($sheet),
                'hasPendingMeetingProposition' => $this->meetingRequestRepository->hasPendingPropositionReceivedBySheet($sheet),
                'hasScheduledMeeting'          => $this->meetingRepository->hasScheduledMeeting($sheet),
                'hasInvoice'                   => $this->invoiceRepository->hasInvoice($sheet),
                'attend'                       => $sheet->attend(),
                'hasGroup'                     => $sheet->hasGroup(),
                'hasSpot'                      => $sheet->getSpot() !== null,
                'availableSlotIds'             => $this->buildAvailableSlots($sheet),
                'reminderDate'                 => null !== $sheet->getReminderDate() ? $sheet->getReminderDate()->format('c') : null
            ],
            $contentByLocale
        ));
    }

    /**
     * @param TemplateObject[] $templatesObjects
     *
     * @return string
     */
    private function getSearchableContent(array $templatesObjects)
    {
        $searchableContent = [];

        foreach ($templatesObjects as $templateObject) {
            if ($templateObject instanceof SearchableObjectInterface) {
                $content = $templateObject->getSearchableContent();

                if (is_array($content)) {
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

    /**
     * @param TemplateData $templateData
     *
     * @return false|string
     */
    private function getCountryCode(TemplateData $templateData)
    {
        return $templateData->getTaggedContentValue(Tag::SHEET_COUNTRY);
    }

    /**
     * @param TemplateData $templateData
     * @param array        $locales
     *
     * @return array
     */
    private function buildCountry(TemplateData $templateData, array $locales)
    {
        $country     = [];
        $countryCode = $this->getCountryCode($templateData);

        if ($countryCode !== false) {
            $regionBundle = Intl::getRegionBundle();

            foreach ($locales as $key => $locale) {
                $countryName = $regionBundle->getCountryName($countryCode, $locale);

                $country[$key]['locale']             = $locale;
                $country[$key]['label']              = $countryName;
                $country[$key]['label_autocomplete'] = $countryName;
            }
        }

        return $country;
    }

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    private function buildKeywords(Sheet $sheet)
    {
        $keywords     = [];
        $keywordIndex = 0;

        foreach ($sheet->getEvent()->getLocales() as $locale) {
            $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);

            foreach ($templateData->getObjects() as $templateObject) {
                if ($templateObject instanceof IndexableObjectInterface) {
                    $content = $templateObject->getSearchableContent();

                    if (is_array($content)) {
                        foreach ($content as $item) {
                            $keywords[$keywordIndex]['label']              = $item;
                            $keywords[$keywordIndex]['label_autocomplete'] = $item;
                            $keywords[$keywordIndex]['locale']             = $locale;
                            $keywordIndex++;
                        }
                    } elseif (null !== $content && !empty($content)) {
                        $keywords[$keywordIndex]['label']              = $content;
                        $keywords[$keywordIndex]['label_autocomplete'] = $content;
                        $keywords[$keywordIndex]['locale']             = $locale;
                        $keywordIndex++;
                    }
                }
            }
        }

        return $keywords;
    }

    /**
     * @param TemplateData $templateData
     *
     * @return null|string
     */
    private function getTwoFirstCharsOfFranceZipcode(TemplateData $templateData)
    {
        $countryCode = $this->getCountryCode($templateData);

        if ('FR' !== $countryCode) {
            return null;
        }

        $zipcode = $templateData->getTaggedContentValue(Tag::SHEET_ZIPCODE);

        if (!$zipcode) {
            return null;
        }

        if (4 === mb_strlen($zipcode)) {
            return '0' . substr($zipcode, 0, 1);
        }

        if (5 === mb_strlen($zipcode)) {
            return substr($zipcode, 0, 2);
        }

        return null;
    }

    /**
     * @param TemplateData $templateData
     *
     * @return null|string
     */
    private function getCity(TemplateData $templateData)
    {
        return $templateData->getTaggedContentValue(Tag::SHEET_CITY) ?: null;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return array
     */
    private function buildParticipants(Sheet $sheet, $locale)
    {
        $participants = array_map(
            function (Participant $participant) use ($locale) {
                return [
                    'email'    => $participant->getUser()->getEmail(),
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
            $sheet->getParticipants()->toArray()
        );

        return $participants;
    }

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    private function buildCategories(Sheet $sheet)
    {
        $categories = array_map(
            function (Category $category) {
                return ['id' => $category->getId()];
            },
            $sheet->getType()->getCategories()->toArray()
        );

        return $categories;
    }

    /**
     * @param TemplateData $data
     * @param string       $objective
     *
     * @return array
     */
    private function buildNomenclatureItems(TemplateData $data, $objective = Nomenclature::OBJECTIVE_NONE)
    {
        $nomenclatureItems   = [];
        $nomenclatureObjects = $data->getNomenclatureObjectsByObjective($objective);

        foreach ($nomenclatureObjects as $nomenclatureObject) {
            $items = $nomenclatureObject->getData();

            if (isset($items['items'])) {
                foreach ($items['items'] as $item) {
                    $nomenclatureItems[]['key'] = $item;
                }
            }
        }

        return $nomenclatureItems;
    }

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    private function buildAvailableSlots(Sheet $sheet)
    {
        $ids = array_map(function (Sheet\AvailableSlot $availableSlot) {
            return ['id' => $availableSlot->getSlot()->getId()];
        }, $sheet->getAvailableSlots());

        return $ids;
    }

    private function getOrderStatus(Sheet $sheet): string
    {
        $orderVatViews = $this->orderBalance->getNotCancelledOrderVatViews($sheet);

        if (empty($orderVatViews)) {
            return Constant::ORDER_STATUS_NO_ORDER;
        }

        $totalWithoutVat = $this->orderBalance->getTotalWithoutVat($sheet);

        if ($totalWithoutVat > 0) {
            return Constant::ORDER_STATUS_TOTAL_ORDER_SUPERIOR_ZERO;
        }

        return Constant::ORDER_STATUS_TOTAL_ORDER_EQUAL_ZERO;
    }
}
