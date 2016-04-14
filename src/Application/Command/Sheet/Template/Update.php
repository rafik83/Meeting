<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Sheet\Template;

class Update
{
    /**
     * @var Template
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
     * @param Template $template
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
        $this->title    = $template->getTitle();
        $this->fallback = $template->getFallback();
    }
}
