<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\SheetTemplate;

use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class Duplicator
{
    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $sheetTemplateRepository;

    /**
     * @var SheetTemplateCloner
     */
    private $sheetTemplateCloner;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @param SheetTemplateRepositoryInterface $sheetTemplateRepository
     * @param SheetTemplateCloner              $sheetTemplateCloner
     * @param TemplateDataFactory              $templateDataFactory
     */
    public function __construct(
        SheetTemplateRepositoryInterface $sheetTemplateRepository,
        SheetTemplateCloner $sheetTemplateCloner,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->sheetTemplateRepository = $sheetTemplateRepository;
        $this->sheetTemplateCloner     = $sheetTemplateCloner;
        $this->templateDataFactory     = $templateDataFactory;
    }

    /**
     * @param Event $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     *
     * @return DuplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage): DuplicatorDataStorage
    {
        $sheetTemplates = $this
            ->sheetTemplateRepository
            ->getTemplateForGivenEvent($event->getDuplicatedFrom())
        ;

        foreach ($sheetTemplates as $sheetTemplate) {
            $clonedTemplate = $this->sheetTemplateCloner->duplicate(
                $sheetTemplate,
                $event,
                $sheetTemplate->getTitle()
            );
            $templateData        = $this->templateDataFactory->createFromTemplate($sheetTemplate);
            $nomenclatureObjects = $templateData->getNomenclatureObjects();

            foreach ($nomenclatureObjects as $nomenclatureObject) {
                $nomenclatureId  = $nomenclatureObject->getNomenclatureId();
                $newNomenclature = $duplicatorDataStorage->nomenclatures[$nomenclatureId];
                $nomenclatureObject->setNomenclature($newNomenclature);
            }

            $clonedTemplate->setValue($templateData->getConfig());
            $this->sheetTemplateRepository->set($clonedTemplate);
            $duplicatorDataStorage->sheetTemplates[$sheetTemplate->getId()] = $clonedTemplate;
        }

        return $duplicatorDataStorage;
    }
}
