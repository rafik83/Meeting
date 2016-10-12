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
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateBooleanFilterIdentifier;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\SearchableObjectInterface;
use Proximum\Vimeet\Infrastructure\Elastica\AvailableLocales;
use Symfony\Component\Intl\Intl;

class SheetElasticTransformer implements ModelToElasticaTransformerInterface
{
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param ParticipantInfoGuesser     $participantInfoGuesser
     * @param CartRowRepositoryInterface $cartRowRepository
     * @param TemplateDataFactory        $templateDataFactory
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        CartRowRepositoryInterface $cartRowRepository,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->cartRowRepository      = $cartRowRepository;
        $this->templateDataFactory    = $templateDataFactory;
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
            $participants = array_map(
                function (Participant $participant) use ($locale) {
                    return [
                        'email'    => $participant->getUser()->getEmail(),
                        'lastname' => $this->participantInfoGuesser->guessParticipantLastName(
                            $participant,
                            $locale
                        ),
                    ];
                },
                $sheet->getParticipants()->toArray()
            );

            if ($sheet->hasUserParticipant($sheet->getOwner())) {
                $participants[] = [
                    'email'    => $sheet->getOwner()->getEmail(),
                    'lastname' => $sheet->getOwner()->getAccount()->getLastName(),
                ];
            }
        }

        try {
            $owner = $sheet->getOwner()->getId();
        } catch (\RuntimeException $e) {
            $owner = null;
        }

        $categories = array_map(
            function (Category $category) {
                return ['id' => $category->getId()];
            },
            $sheet->getType()->getCategories()->toArray()
        );

        $hasCart              = count($this->cartRowRepository->findBySheet($sheet)) > 0;
        $templateData         = $this->templateDataFactory->createRegistrationFromSheet($sheet, $locale);
        $filtersValue         = TemplateBooleanFilterIdentifier::getBooleanFilterValues($templateData);
        $organizationCategory = $templateData->getTaggedContentValue(Tag::SHEET_ORGANIZATION_CATEGORY);

        $content         = [];
        $contentByLocale = [];

        foreach ($sheet->getEvent()->getLocales() as $locale) {
            $data          = $this->templateDataFactory->createFromSheet($sheet, $locale);
            $localeContent = $this->getSearchableContent($data->getObjects());

            // Add locale content in same field
            $content[] = $localeContent;

            // if locale field exists in ES, add it
            if (in_array($locale, AvailableLocales::getAvailableLocalesForContent())) {
                $contentByLocale[sprintf('content_%s', $locale)] = $localeContent;
            }
        }

        return new Document($sheet->getId(), array_merge(
            [
                'id'                   => $sheet->getId(),
                'sheetName'            => $this->sheetInfoGuesser->guessSheetName($sheet, $locale),
                'state'                => $sheet->getState(),
                'completed'            => $sheet->isCompleted(),
                'type'                 => $sheet->getType()->getId(),
                'categories'           => $categories,
                'followUp'             => $sheet->getFollower() instanceof Admin ? $sheet->getFollower()->getId() : null,
                'participantNumber'    => count($sheet->getParticipants()),
                'participants'         => $participants,
                'event'                => $sheet->getEvent()->getId(),
                'owner'                => $owner,
                'createdAt'            => $sheet->getCreatedAt()->format('c'),
                'inCatalog'            => $sheet->isInCatalog(),
                'inCatalogAt'          => null !== $sheet->getInCatalogAt() ? $sheet->getInCatalogAt()->format('c') : null,
                'booleanFilter'        => $filtersValue,
                'hasOrder'             => $sheet->hasNotCancelledOrders(),
                'hasCart'              => $hasCart,
                'organizationCategory' => in_array($organizationCategory, [false, '']) ? null : $organizationCategory,
                'content'              => implode(' ', $content),
                'country'              => $this->getCountryNames($templateData, $sheet->getEvent()->getLocales()),
                'city'                 => $this->getCity($templateData),
                'zipcode'              => $this->getTwoFirstCharsOfFranceZipcode($templateData),
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
     * @return mixed
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
    private function getCountryNames(TemplateData $templateData, array $locales)
    {
        $countryNames = [];
        $countryCode  = $this->getCountryCode($templateData);

        if ($countryCode) {
            $regionBundle = Intl::getRegionBundle();

            foreach ($locales as $locale) {
                $countryNames[$locale] = $regionBundle->getCountryName($countryCode, $locale);
            }
        }

        return $countryNames;
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

        if (4 === strlen($zipcode)) {
            return '0' . substr($zipcode, 0, 1);
        }

        if (5 === strlen($zipcode)) {
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
}
