<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

class Update
{
    /**
     * @var SheetTemplate
     */
    public $template;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $fallback;

    /**
     * Update constructor.
     *
     * @param SheetTemplate $template
     */
    public function __construct(SheetTemplate $template)
    {
        $this->template = $template;
        $this->title    = $template->getTitle();
        $this->fallback = $template->getFallback();
    }
}
