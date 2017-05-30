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
use Proximum\Vimeet\Domain\Model\Type;

class TipTranslationViewQuery
{
    /** @var Event $event */
    public $event;

    /** @var Type */
    public $type;

    /** @var string */
    public $context;

    /** @var string */
    public $locale;

    /**
     * TipTranslationViewQuery constructor.
     *
     * @param Type   $type
     * @param string $context
     * @param string $locale
     */
    public function __construct(Type $type, $context, $locale)
    {
        $this->event   = $type->getEvent();
        $this->type    = $type;
        $this->context = $context;
        $this->locale  = $locale;
    }
}
