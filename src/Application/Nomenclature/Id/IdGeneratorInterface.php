<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Nomenclature\Id;

interface IdGeneratorInterface
{
    /**
     * @return string
     */
    public function generate();
}
