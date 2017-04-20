<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\Sheet;

class Process
{
    /**
     * @var Sheet[]
     */
    public $sheets;

    /**
     * @var Message
     */
    public $message;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var array
     */
    public $placeholders;

    /**
     * Process constructor.
     *
     * @param Message $message
     * @param Sheet[] $sheets
     * @param string  $locale
     * @param array   $placeholders
     */
    public function __construct(Message $message, array $sheets, $locale, array $placeholders = [])
    {
        $this->sheets       = $sheets;
        $this->message      = $message;
        $this->locale       = $locale;
        $this->placeholders = $placeholders;
    }
}
