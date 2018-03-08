<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View;

class ParticipantPositionView
{
    /** @var string */
    public $label;

    /** @var string */
    public $locale;

    public function __construct(string $label, string $locale)
    {
        $this->label = $label;
        $this->locale = $locale;
    }
}
