<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Messaging\Message;

use Proximum\Vimeet\Domain\Model\Event;

class PreviewView
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var string */
    public $subject;

    /** @var string */
    public $content;

    /**
     * @param Event  $event
     * @param string $locale
     * @param string $subject
     * @param string $content
     */
    public function __construct($event, $locale, $subject, $content)
    {
        $this->event   = $event;
        $this->locale  = $locale;
        $this->subject = $subject;
        $this->content = $content;
    }
}
