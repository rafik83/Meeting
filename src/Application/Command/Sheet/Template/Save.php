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

class Save
{
    /**
     * @var SheetTemplate
     */
    public $template;

    /**
     * @var array
     */
    public $value;

    /**
     * Save constructor.
     *
     * @param SheetTemplate $template
     * @param array         $value
     */
    public function __construct(SheetTemplate $template, array $value)
    {
        $this->template = $template;
        $this->value    = $value;
    }
}
