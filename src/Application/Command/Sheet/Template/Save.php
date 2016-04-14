<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

class Save
{
    /**
     * @var SheetTemplate
     */
    public $template;

    /**
     * @var string
     */
    public $value;

    /**
     * Save constructor.
     *
     * @param SheetTemplate $template
     * @param string        $value
     */
    public function __construct(SheetTemplate $template, $value)
    {
        $this->template = $template;
        $this->value    = $value;
    }
}
