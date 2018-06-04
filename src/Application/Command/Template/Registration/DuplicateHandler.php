<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;

class DuplicateHandler
{
    /** @var RegistrationTemplateCloner */
    private $registrationTemplateCloner;

    /**
     * @param RegistrationTemplateCloner $registrationTemplateCloner
     */
    public function __construct(RegistrationTemplateCloner $registrationTemplateCloner)
    {
        $this->registrationTemplateCloner = $registrationTemplateCloner;
    }

    /**
     * @param Duplicate $duplicate
     *
     * @return DuplicateResult
     */
    public function handle(Duplicate $duplicate): DuplicateResult
    {
        $template = $this->registrationTemplateCloner->duplicate(
            $duplicate->registrationTemplate,
            $duplicate->event,
            $duplicate->title
        );

        return new DuplicateResult($template);
    }
}
