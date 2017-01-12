<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Domain\Model\Admin;
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
     * @var bool
     */
    public $isEmailInMapping;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * ImportMapping constructor.
     *
     * @param Event $event
     * @param Type $type
     * @param Admin $admin
     * @param       $locale
     * @param array $csvHeaders
     * @param array $registrationHeaders
     */
    public function __construct(
        Event $event,
        Type $type,
        Admin $admin,
        $locale,
        array $csvHeaders,
        array $registrationHeaders)
    {
        $this->csvHeaders          = $csvHeaders;
        $this->registrationHeaders = $registrationHeaders;
        $this->event               = $event;
        $this->type                = $type;
        $this->locale              = $locale;
        $this->admin = $admin;
    }
}
