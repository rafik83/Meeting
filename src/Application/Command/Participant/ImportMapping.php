<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class ImportMapping
{
    /**
     * @var array
     */
    public $mappings;

    /**
     * @var array
     */
    public $csvHeaders;

    /**
     * @var array
     */
    public $registrationHeaders;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Type
     */
    public $type;

    /**
     * ImportMapping constructor.
     *
     * @param Event $event
     * @param Type  $type
     * @param       $locale
     * @param array $csvHeaders
     * @param array $registrationHeaders
     */
    public function __construct(Event $event, Type $type, $locale, array $csvHeaders, array $registrationHeaders)
    {
        $this->csvHeaders          = $csvHeaders;
        $this->registrationHeaders = $registrationHeaders;
        $this->event               = $event;
        $this->type                = $type;
        $this->locale              = $locale;
    }
}
