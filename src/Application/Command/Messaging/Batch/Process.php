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
     * Process constructor.
     *
     * @param Message $message
     * @param Sheet[] $sheets
     */
    public function __construct(Message $message, array $sheets)
    {
        $this->sheets  = $sheets;
        $this->message = $message;
    }
}
