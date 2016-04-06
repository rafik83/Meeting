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

class Save
{
    /**
     * @var Template
     */
    public $template;

    /**
     * @var string
     */
    public $value;

    /**
     * Save constructor.
     *
     * @param Template $template
     * @param string   $value
     */
    public function __construct(Template $template, $value)
    {
        $this->template = $template;
        $this->value    = $value;
    }
}
