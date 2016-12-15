<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

class WelcomeView
{
    /**
     * @var bool
     */
    public $hasPackage = false;

    /**
     * @var bool
     */
    public $hasProgram = false;

    /**
     * @return int
     */
    public function getColSize()
    {
        if ($this->hasPackage && $this->hasProgram) {
            return 4;
        }

        if ($this->hasPackage || $this->hasProgram) {
            return 6;
        }

        return 12;
    }
}
