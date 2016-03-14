<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Exception;

class InvalidTypeException extends TemplateException
{
    /**
     * InvalidTypeException constructor.
     *
     * @param string          $type
     * @param array           $availableTypes
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($type, array $availableTypes, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Type "%s" is invalid. Available types are "%s"', $type, implode('", "', $availableTypes));

        parent::__construct($message, $code, $previous);
    }
}
