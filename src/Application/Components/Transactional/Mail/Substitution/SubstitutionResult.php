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

    /** @var array of substitutions indexed by placeholder */
    public $subjectSubstitutions;

    /** @var array of substitutions indexed by placeholder */
    public $contentSubstitutions;

    public function __construct(
        string $subject,
        string $content,
        array $subjectSubstitutions = [],
        array $contentSubstitutions = []
    ) {
        $this->subject = $subject;
        $this->content = $content;
        $this->subjectSubstitutions = $subjectSubstitutions;
        $this->contentSubstitutions = $contentSubstitutions;
    }
}
