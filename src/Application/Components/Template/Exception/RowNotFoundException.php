<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Exception;

class RowNotFoundException extends TemplateException
{
    /**
     * RowNotFoundException constructor.
     *
     * @param string          $key
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($key, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf('Row not found for the key "%s".', $key), $code, $previous);
    }
}
