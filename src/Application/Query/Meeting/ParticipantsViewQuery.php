<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantsViewQuery
{
    /** @var Participant[] */
    public $participants;

    /** @var string */
    public $locale;

    /** @var Contact[] */
    public $contacts;

    /**
     * ParticipantsViewQuery constructor.
     *
     * @param Participant[] $participants
     * @param string        $locale
     * @param Contact[]     $contacts
     */
    public function __construct(array $participants, $locale, array $contacts)
    {
        $this->participants = $participants;
        $this->locale = $locale;
        $this->contacts = $contacts;
    }
}
