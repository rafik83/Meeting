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
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class SaveHandler
{
    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     * @param JobQueueInterface                       $jobQueue
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        JobQueueInterface $jobQueue
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param Save $save
     */
    public function handle(Save $save)
    {
        $save->registrationTemplate->setValue($save->value);
        $this->registrationTemplateRepository->set($save->registrationTemplate);
        $this->jobQueue->indexSheetsByRegistrationTemplate($save->registrationTemplate);
    }
}
