<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Transactional\Mail\Customize;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;

class CustomizedMailViewQuery implements Query
{
    /** @var Message */
    public $message;

    /** @var string */
    public $locale;

    public function __construct(Message $message, string $locale)
    {
        $this->message = $message;
        $this->locale = $locale;
    }
}
