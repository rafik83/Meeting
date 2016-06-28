<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum Vimeet
 *
 * @author Elao <contact@elao.com>
 */


namespace Proximum\Vimeet\Application\Template\Registration;


use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class RegistrationTemplateCloner
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
     * @param \DateTimeInterface                      $dateTime
     */
    public function __construct(RegistrationTemplateRepositoryInterface $registrationTemplateRepository, \DateTimeInterface $dateTime)
    {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
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
        $registrationTemplate = new RegistrationTemplate(
            $title,
            $registrationTemplate->getValue(),
            $registrationTemplate->getLocales(),
            $registrationTemplate->getFallback(),
            $this->dateTime
        );
        $registrationTemplate->setEvent($event);

        $this->registrationTemplateRepository->add($registrationTemplate);

        return $registrationTemplate;
    }
}