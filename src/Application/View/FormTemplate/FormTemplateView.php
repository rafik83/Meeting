<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\FormTemplate;

class FormTemplateView
{
    /** @var string */
    public $title;

    /** @var bool */
    public $isPublished;

    public function __construct(string $title, bool $isPublished)
    {
        $this->title = $title;
        $this->isPublished = $isPublished;
    }
}
