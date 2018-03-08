<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Content;

class TermsOfSaleView
{
    /** @var string */
    public $content;

    /**
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }
}
