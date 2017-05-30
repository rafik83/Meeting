<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Domain\Model\Event;

class TipTranslationViewQuery
{
    /** @var Event $event */
    public $event;

    /** @var string */
    public $context;

    /** @var string */
    public $locale;

    /**
     * TipTranslationViewQuery constructor.
     *
     * @param Event  $event
     * @param string $context
     * @param string $locale
     */
    public function __construct(Event $event, $context, $locale)
    {
        $this->event   = $event;
        $this->context = $context;
        $this->locale  = $locale;
    }
}
