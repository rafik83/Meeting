<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\RegistrationTemplate;

use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class Duplicator
{
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $registrationTemplateRepository;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var RegistrationTemplateCloner
     */
    private $registrationTemplateCloner;

    /**
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     * @param TemplateDataFactory                     $templateDataFactory
     * @param RegistrationTemplateCloner              $registrationTemplateCloner
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        TemplateDataFactory $templateDataFactory,
        RegistrationTemplateCloner $registrationTemplateCloner
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->templateDataFactory            = $templateDataFactory;
        $this->registrationTemplateCloner     = $registrationTemplateCloner;
    }

    /**
     * @param Event $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     *
     * @return DuplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage): DuplicatorDataStorage
    {
        $registrationTemplates = $this
            ->registrationTemplateRepository
            ->getTemplateForGivenEvent($event->getDuplicatedFrom());

        foreach ($registrationTemplates as $registrationTemplate) {
            $clonedTemplate = $this->registrationTemplateCloner->duplicate(
                $registrationTemplate,
                $event,
                $registrationTemplate->getTitle()
            );

            $templateData        = $this->templateDataFactory->createFromTemplate($registrationTemplate);
            $nomenclatureObjects = $templateData->getNomenclatureObjects();

            foreach ($nomenclatureObjects as $nomenclatureObject) {
                $nomenclatureId  = $nomenclatureObject->getNomenclatureId();
                $newNomenclature = $duplicatorDataStorage->nomenclatures[$nomenclatureId];
                $nomenclatureObject->setNomenclature($newNomenclature);
            }

            $clonedTemplate->setValue($templateData->getConfig());
            $this->registrationTemplateRepository->set($clonedTemplate);
            $duplicatorDataStorage->registrationTemplates[$registrationTemplate->getId()] = $clonedTemplate;
        }

        return $duplicatorDataStorage;
    }
}
