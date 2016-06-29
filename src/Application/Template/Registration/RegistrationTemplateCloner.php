<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum Vimeet
 *
 * @author Elao <contact@elao.com>
 */


namespace Proximum\Vimeet\Application\Template\Registration;


use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class RegistrationTemplateCloner
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
     * @var NomenclatureCloner
     */
    private $nomenclatureCloner;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * RegistrationTemplateCloner constructor.
     *
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     * @param TemplateDataFactory                     $templateDataFactory
     * @param NomenclatureCloner                      $nomenclatureCloner
     * @param \DateTimeInterface                      $dateTime
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        TemplateDataFactory $templateDataFactory,
        NomenclatureCloner $nomenclatureCloner,
        \DateTimeInterface $dateTime
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->templateDataFactory            = $templateDataFactory;
        $this->nomenclatureCloner             = $nomenclatureCloner;
        $this->dateTime                       = $dateTime;
    }

    /**
     * @param RegistrationTemplate $registrationTemplate
     * @param Event                $event
     * @param string               $title
     *
     * @return RegistrationTemplate
     */
    public function duplicate(RegistrationTemplate $registrationTemplate, Event $event, $title)
    {
        // Clone template
        $registrationTemplate = new RegistrationTemplate(
            $title,
            $registrationTemplate->getValue(),
            $registrationTemplate->getLocales(),
            $registrationTemplate->getFallback(),
            $this->dateTime
        );

        // We have to keep the original event to find the right nomenclatures in the template data builder
        $registrationTemplate->setEvent($registrationTemplate->getEvent());

        // Clone nomenclature
        $template = $this->cloneNomenclatures($event, $registrationTemplate);

        // Update template
        $registrationTemplate->setValue($template->getConfig());

        // Now we can set the proper event
        $registrationTemplate->setEvent($event);

        // Save
        $this->registrationTemplateRepository->add($registrationTemplate);

        return $registrationTemplate;
    }

    /**
     * @param Event         $event
     * @param RegistrationTemplate $registrationTemplate
     *
     * @return TemplateData
     */
    private function cloneNomenclatures(Event $event, RegistrationTemplate $registrationTemplate)
    {
        $template = $this->templateDataFactory->createFromRegistrationTemplate($registrationTemplate);
        $objects  = $template->getNomenclatureObjects();

        foreach ($objects as $object) {
            if ($object->getNomenclatureModel()->getEvent() !== $event) {
                $original = $object->getNomenclatureModel();
                $clone    = $this->nomenclatureCloner->duplicateIfNotExists($original, $event);

                $object->setNomenclature($clone);
            }
        }

        return $template;
    }
}
