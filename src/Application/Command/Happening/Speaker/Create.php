<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Create
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $function;

    /**
     * @var string
     */
    public $organization;

    /**
     * @var UploadedFile
     */
    public $logo;

    /**
     * @var UploadedFile
     */
    public $photo;

    /**
     * Create constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
