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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ParticipantsViewQuery
{
    /** @var Participant[] */
    public $participants;

    /** @var string */
    public $locale;

    /** @var Contact[] */
    public $contacts;

    /** @var Sheet */
    public $seerSheet;

    /** @var User */
    public $seerUser;

    /**
     * @param Participant[] $participants
     * @param Contact[]     $contacts
     */
    public function __construct(array $participants, string $locale, array $contacts, Sheet $seerSheet, User $seerUser)
    {
        $this->participants = $participants;
        $this->locale = $locale;
        $this->contacts = $contacts;
        $this->seerSheet = $seerSheet;
        $this->seerUser = $seerUser;
    }
}
