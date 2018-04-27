<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Spot;

class PropertyNotSupportedException extends SpotException
{
    /**
     * PropertyNotExistsException constructor.
     *
     * @param string     $property
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($property, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf('Property "%s" does not exist.', $property), $code, $previous);
    }
}
