<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;

class RefusedRequest
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var string
     */
    public $refuseMessage;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->request->setState(Request::STATE_REFUSED);
    }
}
