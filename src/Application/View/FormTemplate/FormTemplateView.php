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

    /** @var null|string */
    public $status;

    public function __construct(string $title, ?string $status)
    {
        $this->title = $title;
        $this->status = $status;
    }
}
