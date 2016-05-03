<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Object;

use Proximum\Vimeet\Domain\Template\Object;

class Telephone extends Object
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getData() ? $this->getData() : '';
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function validateData(array $data)
    {
        $pattern = '/^((\+|00)\d{1,3})?\d+$/';

        if (isset($data[$this->getKey()]) && !preg_match($pattern, $data[$this->getKey()])) {
            return false;
        }

        return true;
    }
}
