<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Meeting;

use Proximum\Vimeet\Domain\Model\Sheet;
use Doctrine\Common\Collections\ArrayCollection;

interface MessageSubjectInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return Sheet
     */
    public function getFromSheet();

    /**
     * @return ArrayCollection
     */
    public function getFromParticipants();

    /**
     * @return Sheet
     */
    public function getToSheet();

    /**
     * @return ArrayCollection
     */
    public function getToParticipants();
}
