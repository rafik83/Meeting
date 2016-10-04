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
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\SearchableObjectInterface;

class SheetElasticTransformer implements ModelToElasticaTransformerInterface
{
    private $availableLocaleForContent = ['fr', 'en'];

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

        $contentByLocale = [];

        foreach ($sheet->getEvent()->getLocales() as $locale) {
            if (!in_array($locale, $this->availableLocaleForContent)) {
                throw new \Exception(sprintf('Locale %s is not available in elastic search content index', $locale));
            }

            $data = $this->templateDataFactory->createFromSheet($sheet, $locale);
            $contentByLocale[sprintf('content_%s', $locale)] = $this->getSearchableContent($data->getObjects());
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
}
