<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class RegistrationTemplateManager
{
    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    /**
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     */
    public function __construct(RegistrationTemplateRepositoryInterface $registrationTemplateRepository)
    {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
    }

    /**
     * @param Event $event
     *
     * @return RegistrationTemplate
     */
    public function create(Event $event)
    {
        $registrationTemplate = new RegistrationTemplate(
            'RegistrationTemplate',
            [],
            $event->getLocales(),
            $event->getFallback(),
            new \DateTime(),
            $event
        );

        $this->registrationTemplateRepository->add($registrationTemplate);

        return $registrationTemplate;
    }
}
