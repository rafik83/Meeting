<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Event;

class QRCodePayloadListView
{
    /** @var QRCodePayloadView[] */
    public $list;

    public function __construct(array $list)
    {
        $this->list = $list;
    }
}
