<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Happening;

use Proximum\Vimeet\Domain\Model\Happening\Speaker;

interface SpeakerRepositoryInterface
{
    /**
     * @param Speaker $speaker
     */
    public function add(Speaker $speaker);

    /**
     * @param Speaker $speaker
     */
    public function set(Speaker $speaker);
}
