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
use Proximum\Vimeet\Application\Template\TemplateCloner;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class RegistrationTemplateCloner extends TemplateCloner
{
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $registrationTemplateRepository;

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
        parent::__construct($templateDataFactory, $nomenclatureCloner);

        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->dateTime                       = $dateTime;
    }

    /**
     * @param RegistrationTemplate $template
     * @param Event                $event
     * @param string               $title
     *
     * @return RegistrationTemplate
     */
    public function duplicate(RegistrationTemplate $template, Event $event, $title)
    {
        $clone = new RegistrationTemplate(
            $title,
            $template->getValue(),
            $template->getLocales(),
            $template->getFallback(),
            $this->dateTime,
            $template->getEvent()
        );

        $this->switchEvent($event, $clone);

        $this->registrationTemplateRepository->add($clone);

        return $clone;
    }
}
