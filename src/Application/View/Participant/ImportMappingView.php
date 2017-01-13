<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Participant;

class ImportMappingView
{
    /**
     * Array of CSV headers column
     *
     * @var array
     */
    public $csvHeaders;

    /**
     * Array of template registration block keys
     *
     * @var array
     */
    public $registrationHeaders;

    /**
     * ImportMappingView constructor.
     *
     * @param array $csvHeaders
     * @param array $registrationHeaders
     */
    public function __construct(array $csvHeaders, array $registrationHeaders)
    {
        $this->csvHeaders          = $csvHeaders;
        $this->registrationHeaders = $registrationHeaders;
    }
}
