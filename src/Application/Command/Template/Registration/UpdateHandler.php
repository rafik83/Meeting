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
use Proximum\Vimeet\Domain\Exception\Nomenclature\NomenclatureNotFoundException;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateHandler
{
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $registrationTemplateRepository;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     * @param TemplateDataFactory                     $templateDataFactory
     * @param DelayedEventDispatcher                  $eventDispatcher
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        TemplateDataFactory $templateDataFactory,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->templateDataFactory            = $templateDataFactory;
        $this->eventDispatcher                = $eventDispatcher;
    }

    /**
     * @param Update $update
     *
     * @throws NomenclatureNotFoundException
     */
    public function handle(Update $update)
    {
        $registrationTemplate = $update->registrationTemplate;

        $registrationTemplate->setTitle($update->title);
        $registrationTemplate->setValue($update->value);

        try {
            $this->templateDataFactory->createFromTemplate($registrationTemplate);
        } catch (\RuntimeException $exception) {
            throw new NomenclatureNotFoundException($exception->getMessage());
        }

        $this->registrationTemplateRepository->set($registrationTemplate);

        if (null !== $update->registrationTemplate->getEvent()) {
            $this->eventDispatcher->dispatch(
                Events::REGISTRATION_TEMPLATE_UPDATED,
                new RegistrationTemplateUpdatedEvent($registrationTemplate->getEvent())
            );
        }
    }
}
