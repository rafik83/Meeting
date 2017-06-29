<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Catalog\External;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class CatalogVisibilityView
{
    /** @var Event */
    public $event;

    /** @var Type[] */
    public $types;

    /** @var Category[] */
    public $categories;

    /**
     * CatalogVisibilityView constructor.
     *
     * @param Event      $event
     * @param Type[]     $types
     * @param Category[] $categories
     */
    public function __construct(Event $event, array $types, array $categories)
    {
        $this->event = $event;
        $this->types = $types;
        $this->categories = $categories;
    }
}
