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

class Duplicate
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var Template
     */
    public $template;

    /**
     * Duplicate constructor.
     *
     * @param Template $template
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
    }
}
