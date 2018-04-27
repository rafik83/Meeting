<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

interface WhoInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getIdentifier();
}
