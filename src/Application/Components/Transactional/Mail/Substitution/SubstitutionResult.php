<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

class SubstitutionResult
{
    /** @var string */
    public $subject;

    /** @var string */
    public $content;

    public function __construct(string $subject, string $content)
    {
        $this->subject = $subject;
        $this->content = $content;
    }
}
