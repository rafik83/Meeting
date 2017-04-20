<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Domain\Model\Event;

class CreateMessage
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $emailTemplate;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var array
     */
    public $subjectParameters;

    /**
     * Create constructor.
     *
     * @param Event  $event
     * @param string $name
     * @param string $subject
     * @param array  $subjectParameters
     * @param string $emailTemplate
     * @param string $locale
     */
    public function __construct(Event $event, $name, $subject, array $subjectParameters, $emailTemplate, $locale)
    {
        $this->event             = $event;
        $this->subject           = $subject;
        $this->subjectParameters = $subjectParameters;
        $this->emailTemplate     = $emailTemplate;
        $this->locale            = $locale;
        $this->name              = $name;
    }
}
