<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Exception\Template\RegistrationTemplateException;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SaveHandler
{
    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    /** @var JobQueueInterface */
    private $jobQueue;
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        JobQueueInterface $jobQueue,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->jobQueue = $jobQueue;
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param Save $save
     *
     * @throws RegistrationTemplateException
     */
    public function handle(Save $save)
    {
        $save->registrationTemplate->setValue($save->value);

        try {
            $this->templateDataFactory->createRegistrationFromTemplate(
                $save->registrationTemplate,
                $save->registrationTemplate->getFallback()
            );
        } catch (\Exception $exception) {
            throw new RegistrationTemplateException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->registrationTemplateRepository->set($save->registrationTemplate);
        $this->jobQueue->indexSheetsByRegistrationTemplate($save->registrationTemplate);
    }
}
