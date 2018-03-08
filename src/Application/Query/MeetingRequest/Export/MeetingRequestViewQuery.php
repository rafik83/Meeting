<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\MeetingRequest\Export;

use Proximum\Vimeet\Domain\Model\Meeting\Request;

class MeetingRequestViewQuery
{
    /** @var Request */
    public $request;

    /** @var string */
    public $locale;

    /**
     * @param Request $request
     * @param string  $locale
     */
    public function __construct(Request $request, string $locale)
    {
        $this->request = $request;
        $this->locale = $locale;
    }
}
