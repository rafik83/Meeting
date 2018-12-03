<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Template\Form;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Template\TemplateData;

class BreadCrumbViewQuery implements Query
{
    /** @var TemplateData */
    public $templateData;

    /** @var int */
    public $currentStep;

    /** @var string */
    public $locale;

    public function __construct(
        TemplateData $templateData,
        int $currentStep,
        string $locale
    ) {
        $this->templateData = $templateData;
        $this->currentStep = $currentStep;
        $this->locale = $locale;
    }
}
