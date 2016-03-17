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
     * @param array           $availables
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($key, array $availables, $code = 0, \Exception $previous = null)
    {
        $message = sprintf('Row not found for the key "%s". Availables are "%s".', $key, implode('", "', $availables));

        parent::__construct($message, $code, $previous);
    }
}
