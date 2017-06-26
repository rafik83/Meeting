<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Proximum\Vimeet\Domain\View\CategoryView;

class Configure
{
    /** @var Event */
    public $event;

    /** @var TypeView[] */
    public $types;

    /** @var CategoryView[] */
    public $categories;

    /** @var bool */
    public $catalogPublic;

    /**
     * Configure constructor.
     *
     * @param Event $event
     * @param bool $catalogPublic
     */
    public function __construct(Event $event, bool $catalogPublic = false)
    {
        $this->event = $event;
        $this->catalogPublic = $catalogPublic;
    }
}
