<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip\Event;

use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;

class PreviewTipViewQuery
{
    /** @var TipTranslation */
    public $tipTranslation;

    /** @var array */
    public $pages;

    /**
     * PreviewTipViewQuery constructor.
     *
     * @param TipTranslation $tipTranslation
     */
    public function __construct(TipTranslation $tipTranslation)
    {
        $this->tipTranslation = $tipTranslation;
        $this->pages = $tipTranslation->getTip()->getPagesTranslations();
    }
}
