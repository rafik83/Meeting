<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Template\Registration;


use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Registration\RegistrationTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Filter\BooleanTemplateFilter;
use Proximum\Vimeet\Domain\Repository\Filter\BooleanTemplateFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateBooleanFilterIdentifier;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
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
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @param RegistrationTemplateRepositoryInterface  $registrationTemplateRepository
     * @param BooleanTemplateFilterRepositoryInterface $booleanTemplateFilterRepository
     * @param TemplateDataFactory                      $templateDataFactory
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        BooleanTemplateFilterRepositoryInterface $booleanTemplateFilterRepository,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->registrationTemplateRepository  = $registrationTemplateRepository;
        $this->booleanTemplateFilterRepository = $booleanTemplateFilterRepository;
        $this->templateDataFactory             = $templateDataFactory;
    }

    /**
     * @param RegistrationTemplateUpdatedEvent $event
     */
    public function generateBooleanFilter(RegistrationTemplateUpdatedEvent $event)
    {
        $this->booleanTemplateFilterRepository->deleteForEvent($event->getEvent());
        $templates = $this->registrationTemplateRepository->getTemplateForGivenEvent($event->getEvent());

        $templatesData = [];

        foreach ($templates as $template) {
            $templatesData[] = $this->templateDataFactory->createFromTemplate($template);
        }

        $filters = TemplateBooleanFilterIdentifier::getBooleanFilters($templatesData);

        $filtersPrepared = [];

        foreach ($filters as $filter) {
            // Avoid duplicate filter
            if (!isset($filtersPrepared[$filter['key']])) {
                $booleanFilter = new BooleanTemplateFilter(
                    $event->getEvent(),
                    $filter['key'],
                    $filter['value']
                );

                $this->booleanTemplateFilterRepository->add($booleanFilter);
                $filtersPrepared[$booleanFilter->getTemplateKey()] = $booleanFilter;
            }
        }
    }


    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::REGISTRATION_TEMPLATE_UPDATED => 'generateBooleanFilter',
        ];
    }
}
