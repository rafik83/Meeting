<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Participant;

class ImportMappingView
{
    /**
     * Array of field headers column
     *
     * @var array
     */
    public $fieldHeaders;

    /**
     * Array of template registration block keys
     *
     * @var array
     */
    public $registrationHeaders;

    /**
     * ImportMappingView constructor.
     *
     * @param array $fieldHeaders
     * @param array $registrationHeaders
     */
    public function __construct(array $fieldHeaders, array $registrationHeaders)
    {
        $this->fieldHeaders        = $fieldHeaders;
        $this->registrationHeaders = $registrationHeaders;
    }
}
