<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Registration\RegistrationTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateHandler
{
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $registrationTemplateRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     * @param EventDispatcherInterface                $eventDispatcher
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->eventDispatcher                = $eventDispatcher;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $registrationTemplate = $update->registrationTemplate;
        $registrationTemplate->setTitle($update->title);
        $registrationTemplate->setValue($update->value);

        $this->registrationTemplateRepository->set($registrationTemplate);

        if (null !== $update->registrationTemplate->getEvent()) {
            $this->eventDispatcher->dispatch(
                Events::REGISTRATION_TEMPLATE_UPDATED,
                new RegistrationTemplateUpdatedEvent($registrationTemplate->getEvent())
            );
        }
    }
}
