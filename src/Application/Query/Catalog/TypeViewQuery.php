<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class TypeViewQuery
{
    /** @var Event */
    public $event;

    /** @var Type[] */
    public $visibleTypes;

    /** @var string */
    public $locale;

    /**
     * @param Event  $event
     * @param Type[] $visibleTypes
     * @param string $locale
     */
    public function __construct(Event $event, array $visibleTypes, $locale)
    {
        $this->event        = $event;
        $this->visibleTypes = $visibleTypes;
        $this->locale       = $locale;
    }
}
