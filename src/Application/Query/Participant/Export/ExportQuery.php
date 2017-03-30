<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant\Export;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;

class ExportQuery
{
    /** @var Event */
    public $event;

    /** @var array */
    public $filters;

    /** @var string */
    public $locale;

    /** @var string */
    public $charset;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $locale
     * @param string $charset
     */
    public function __construct(Event $event, array $filters, $locale, $charset = Charset::WINDOWS_1252)
    {
        $this->event   = $event;
        $this->filters = $filters;
        $this->locale  = $locale;
        $this->charset = $charset;
    }
}
