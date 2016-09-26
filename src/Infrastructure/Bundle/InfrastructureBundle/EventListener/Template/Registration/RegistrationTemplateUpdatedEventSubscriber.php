<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Template\Registration;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Registration\RegistrationTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\BooleanTemplateFilter;
use Proximum\Vimeet\Domain\Model\Filter\TaggedNomenclatureFilter;
use Proximum\Vimeet\Domain\Repository\Filter\BooleanTemplateFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Filter\TaggedNomenclatureFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateBooleanFilterIdentifier;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationTemplateUpdatedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $registrationTemplateRepository;

    /**
     * @var BooleanTemplateFilterRepositoryInterface
     */
    private $booleanTemplateFilterRepository;

    /**
     * @var TaggedNomenclatureFilterRepositoryInterface
     */
    private $taggedNomenclatureFilterRepository;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @param RegistrationTemplateRepositoryInterface     $registrationTemplateRepository
     * @param BooleanTemplateFilterRepositoryInterface    $booleanTemplateFilterRepository
     * @param TaggedNomenclatureFilterRepositoryInterface $taggedNomenclatureFilterRepository
     * @param TemplateDataFactory                         $templateDataFactory
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        BooleanTemplateFilterRepositoryInterface $booleanTemplateFilterRepository,
        TaggedNomenclatureFilterRepositoryInterface $taggedNomenclatureFilterRepository,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->registrationTemplateRepository     = $registrationTemplateRepository;
        $this->booleanTemplateFilterRepository    = $booleanTemplateFilterRepository;
        $this->taggedNomenclatureFilterRepository = $taggedNomenclatureFilterRepository;
        $this->templateDataFactory                = $templateDataFactory;
    }

    /**
     * @param RegistrationTemplateUpdatedEvent $event
     */
    public function onRegistrationTemplateUpdated(RegistrationTemplateUpdatedEvent $event)
    {
        $templates     = $this->registrationTemplateRepository->getUsedTemplateForGivenEvent($event->getEvent());
        $templatesData = [];

        foreach ($templates as $template) {
            $templatesData[] = $this->templateDataFactory->createFromTemplate($template);
        }

        $this->generateBooleanFilter($event->getEvent(), $templatesData);
        $this->saveNomenclaturesOfSheetOrganizationCategories($event->getEvent(), $templatesData);
    }

    /**
     * @param Event          $event
     * @param TemplateData[] $templatesData
     */
    private function generateBooleanFilter(Event $event, array $templatesData)
    {
        $this->booleanTemplateFilterRepository->deleteForEvent($event);

        $filters = TemplateBooleanFilterIdentifier::getBooleanFilters($templatesData);

        $filtersAdded = [];

        foreach ($filters as $filter) {
            // Avoid duplicate filter
            if (!isset($filtersAdded[$filter['key']])) {
                $booleanFilter = new BooleanTemplateFilter(
                    $event,
                    $filter['key'],
                    $filter['value']
                );

                $this->booleanTemplateFilterRepository->add($booleanFilter);
                $filtersAdded[$booleanFilter->getTemplateKey()] = true;
            }
        }
    }

    /**
     * @param Event          $event
     * @param TemplateData[] $templatesData
     */
    private function saveNomenclaturesOfSheetOrganizationCategories(Event $event, array $templatesData)
    {
        $nomenclaturesAdded = [];

        foreach ($templatesData as $templateData) {
            foreach ($templateData->getObjects() as $templateObject) {
                if ($templateObject instanceof Nomenclature
                    && $templateObject->hasTag(Tag::SHEET_ORGANIZATION_CATEGORY)
                ) {
                    $nomenclatureId = $templateObject->getNomenclatureId();

                    if (false === in_array($nomenclatureId, $nomenclaturesAdded)) {
                        $nomenclaturesAdded[] = $nomenclatureId;
                    }
                }
            }
        }

        $this->taggedNomenclatureFilterRepository->deleteForEvent($event);

        if (count($nomenclaturesAdded)) {
            $this->taggedNomenclatureFilterRepository->add(
                new TaggedNomenclatureFilter($event, Tag::SHEET_ORGANIZATION_CATEGORY, $nomenclaturesAdded)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::REGISTRATION_TEMPLATE_UPDATED => 'onRegistrationTemplateUpdated',
        ];
    }
}
