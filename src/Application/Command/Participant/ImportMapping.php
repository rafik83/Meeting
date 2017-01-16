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
     * A mapping array of csv headers keys and their registration headers key
     *
     * @var array
     */
    public $mappings;

    /**
     * Array of CSV headers column
     *
     * @var array
     */
    public $csvHeaders;

    /**
     * Array of template registration block keys
     *
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
     * @var Admin
     */
    public $admin;

    /**
     * ImportMapping constructor.
     *
     * @param Event  $event
     * @param Type   $type
     * @param Admin  $admin
     * @param string $locale
     * @param array  $csvHeaders
     * @param array  $registrationHeaders
     */
    public function __construct(
        Event $event,
        Type $type,
        Admin $admin,
        $locale,
        array $csvHeaders,
        array $registrationHeaders
    ) {
        $this->csvHeaders          = $csvHeaders;
        $this->registrationHeaders = $registrationHeaders;
        $this->event               = $event;
        $this->type                = $type;
        $this->locale              = $locale;
        $this->admin               = $admin;
    }

    /**
     * @return bool
     */
    public function isEmailInMappings()
    {
        return in_array(ParticipantImportTag::REGISTRATION_FIELD_MAIL, $this->mappings, true);
    }

    /**
     * @return bool
     */
    public function isOnlyOneEmailMapping()
    {
        $mappingsValuesCount = array_count_values($this->mappings);

        return $mappingsValuesCount[ParticipantImportTag::REGISTRATION_FIELD_MAIL] <= 1;
    }
}
