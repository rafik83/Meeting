<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Preview;

class TagView
{
    /** @var string */
    public $type;

    /** @var string|null */
    public $label;

    /** @var string */
    public $content;

    /** @var string|null */
    public $originalUrl;

    public function __construct(string $type, string $label, string $content, ?string $originalUrl)
    {
        $this->type = $type;
        $this->label = $label;
        $this->content = $content;
        $this->originalUrl = $originalUrl;
    }

    public function isLink(): bool
    {
        return 'url' === $this->type;
    }
}
