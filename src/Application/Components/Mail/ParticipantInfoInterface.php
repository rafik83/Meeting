<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Mail;

use Proximum\Vimeet\Domain\Model\Event;

interface ParticipantInfoInterface
{
    /**
     * @return string
     */
    public function getFirstname();

    /**
     * @return string
     */
    public function getLastname();

    /**
     * @return Event
     */
    public function getEvent();

    /**
     * @return string
     */
    public function getParticipantType();
}
