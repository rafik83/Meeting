<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Nomenclature;

interface ImporterInterface
{
    /**
     * @param string $title
     * @param mixed  $value
     */
    public function import($title, $value);
}
