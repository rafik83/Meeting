<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Registration\RegistrationTemplateUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Template\RegistrationTemplateException;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Registration\RegistrationTemplateValidator;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateHandler
{
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $registrationTemplateRepository;

    /**
     * @var RegistrationTemplateValidator
     */
    private $registrationTemplateValidator;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var JobQueueInterface
     */
    private $jobQueue;

    /**
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     * @param TemplateDataFactory                     $templateDataFactory
     * @param RegistrationTemplateValidator           $registrationTemplateValidator
     * @param DelayedEventDispatcher                  $eventDispatcher
     * @param JobQueueInterface                       $jobQueue
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        TemplateDataFactory $templateDataFactory,
        RegistrationTemplateValidator $registrationTemplateValidator,
        DelayedEventDispatcher $eventDispatcher,
        JobQueueInterface $jobQueue
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->registrationTemplateValidator = $registrationTemplateValidator;
        $this->eventDispatcher = $eventDispatcher;
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param Update $update
     *
     * @throws RegistrationTemplateException
     */
    public function handle(Update $update)
    {
        $registrationTemplate = $update->registrationTemplate;

        $registrationTemplate->setTitle($update->title);
        $registrationTemplate->setValue($update->value);

        try {
            $templateData = $this->templateDataFactory->createRegistrationFromTemplate(
                $registrationTemplate,
                $registrationTemplate->getFallback()
            );

            $this->registrationTemplateValidator->validate($templateData);
        } catch (\Exception $exception) {
            throw new RegistrationTemplateException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->registrationTemplateRepository->set($registrationTemplate);

        $this->jobQueue->indexSheetsByRegistrationTemplate($registrationTemplate);

        if (null !== $update->registrationTemplate->getEvent()) {
            $this->eventDispatcher->dispatch(
                Events::REGISTRATION_TEMPLATE_UPDATED,
                new RegistrationTemplateUpdatedEvent($registrationTemplate->getEvent())
            );
        }
    }
}
