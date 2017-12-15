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
use Proximum\Vimeet\Application\Components\Registration\RegistrationTemplateValidatorTranslated;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateException;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SaveHandler
{
    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var RegistrationTemplateValidatorTranslated */
    private $registrationTemplateValidatorTranslated;

    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        JobQueueInterface $jobQueue,
        TemplateDataFactory $templateDataFactory,
        RegistrationTemplateValidatorTranslated $registrationTemplateValidatorTranslated
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->jobQueue = $jobQueue;
        $this->templateDataFactory = $templateDataFactory;
        $this->registrationTemplateValidatorTranslated = $registrationTemplateValidatorTranslated;
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
            $templateData = $this->templateDataFactory->createRegistrationFromTemplate(
                $save->registrationTemplate,
                $save->registrationTemplate->getFallback()
            );

            $this->registrationTemplateValidatorTranslated->validate($templateData);
        } catch (\Exception $exception) {
            throw new RegistrationTemplateException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->registrationTemplateRepository->set($save->registrationTemplate);
        $this->jobQueue->indexSheetsByRegistrationTemplate($save->registrationTemplate);
    }
}
