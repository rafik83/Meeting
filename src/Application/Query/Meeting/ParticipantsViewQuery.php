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

    /**
     * ParticipantsViewQuery constructor.
     *
     * @param Participant[] $participants
     * @param string        $locale
     * @param Contact[]     $contacts
     * @param Sheet         $seerSheet
     */
    public function __construct(array $participants, $locale, array $contacts, Sheet $seerSheet)
    {
        $this->participants = $participants;
        $this->locale = $locale;
        $this->contacts = $contacts;
        $this->seerSheet = $seerSheet;
    }
}
