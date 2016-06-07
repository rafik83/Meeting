<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

class ParticipantsView
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var ParticipantView[]
     */
    public $participants;

    /**
     * @param string $title
     * @param string $description
     * @param array  $participants
     */
    public function __construct($title, $description, array $participants)
    {
        $this->title        = $title;
        $this->description  = $description;
        $this->participants = $participants;
    }
}
