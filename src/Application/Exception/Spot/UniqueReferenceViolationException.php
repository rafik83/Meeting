<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Spot;

class UniqueReferenceViolationException extends SpotException
{
    /**
     * UniqueReferenceViolationException constructor.
     *
     * @param string          $reference
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($reference, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf('The reference "%s" already exists.', $reference), $code, $previous);
    }
}
