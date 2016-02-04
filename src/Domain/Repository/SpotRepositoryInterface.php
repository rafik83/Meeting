<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Spot;

interface SpotRepositoryInterface
{
    /**
     * @param Spot $spot
     */
    public function add(Spot $spot);

    /**
     * @param Spot $spot
     */
    public function set(Spot $spot);
}