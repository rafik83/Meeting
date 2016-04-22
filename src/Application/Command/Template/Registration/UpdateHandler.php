<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class UpdateHandler
{
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $registrationTemplateRepository;

    /**
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     */
    public function __construct(RegistrationTemplateRepositoryInterface $registrationTemplateRepository)
    {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
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
    }
}
