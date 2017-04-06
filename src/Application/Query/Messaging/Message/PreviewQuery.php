<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Messaging\Message;

use Proximum\Vimeet\Domain\Model\Messaging\Message;

class PreviewQuery
{
    /** @var Message */
    public $message;

    /** @var string */
    public $locale;

    /**
     * @param Message $message
     * @param string  $locale
     */
    public function __construct(Message $message, $locale)
    {
        $this->message = $message;
        $this->locale  = $locale;
    }
}
