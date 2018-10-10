<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Transactional\Mail\Generic;

class GenericMailView
{
    /** @var string */
    public $key;

    /** @var string */
    public $subject;

    /** @var bool */
    public $isCustomizableByTypes;

    /** @var string[] */
    public $associatedTypeTitles;

    public function __construct(
        string $key,
        string $subject,
        bool $isCustomizableByTypes,
        array $associatedTypeTitles
    ) {
        $this->key = $key;
        $this->subject = $subject;
        $this->isCustomizableByTypes = $isCustomizableByTypes;
        $this->associatedTypeTitles = $associatedTypeTitles;
    }
}
