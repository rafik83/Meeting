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
     * @var array
     */
    public $csvHeaders;

    /**
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
