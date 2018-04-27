<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Template;

class ResolvedPrintTemplateView
{
    /** @var array */
    public $printValueResolved = [];

    /** @var array */
    public $missingObjects = [];

    /**
     * @param array $printValueResolved
     * @param array $missingObjects
     */
    public function __construct(array $printValueResolved, array $missingObjects)
    {
        $this->printValueResolved = $printValueResolved;
        $this->missingObjects     = $missingObjects;
    }
}
