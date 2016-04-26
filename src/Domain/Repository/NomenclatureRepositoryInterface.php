<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;

interface NomenclatureRepositoryInterface
{
    /**
     * @return Nomenclature[]
     */
    public function getAll();

    /**
     * @param Event|int $event
     *
     * @return Nomenclature
     */
    public function findByEvent($event);
}
