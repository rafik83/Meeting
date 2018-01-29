<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Proximum\Vimeet\Domain\Template\TemplateData;

class SheetRegistrationInfoQuery
{
    /** @var TemplateData */
    public $templateData;

    /** @var string */
    public $locale;

    /** @var string */
    public $fallback;

    /**
     * @param TemplateData $templateData
     * @param string       $locale
     * @param string       $fallback
     */
    public function __construct(
        TemplateData $templateData,
        string $locale,
        string $fallback
    ) {
        $this->templateData = $templateData;
        $this->locale = $locale;
        $this->fallback = $fallback;
    }
}
