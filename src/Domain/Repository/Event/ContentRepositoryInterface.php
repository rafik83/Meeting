<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
    public function add(Content $content);

    /**
     * @param Content $content
     */
    public function set(Content $content);

    /**
     * @param Event  $event
     * @param string $type
     *
     * @return Content|null
     */
    public function findByEventAndType(Event $event, $type);
}
