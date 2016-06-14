<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Content;

interface ContentRepositoryInterface
{
    /**
     * @param Content $content
     */
    public function set(Content $content);

    /**
     * @param Event $event
     *
     * @return Content|null
     */
    public function getTermsOfSalesByEvent(Event $event);
}
