<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Spot;

class UniqueReferenceViolationException extends SpotException
{
    /**
     * @var string
     */
    private $reference;

    /**
     * UniqueReferenceViolationException constructor.
     *
     * @param string          $reference
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($reference, $code = 0, \Exception $previous = null)
    {
        $this->reference = $reference;
        parent::__construct(sprintf('The reference "%s" already exists.', $reference), $code, $previous);
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }
}
