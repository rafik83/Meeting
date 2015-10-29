<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command;

use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;

class BaseHandler
{
    /**
     * @param array   $data
     * @param string  $template
     *
     * @throws RequiredDataEmptyException
     */
    public function checkDataConstraint(array $data, $template)
    {
        foreach ($data as $key => $value) {
            if (isset($template[$key]['required']) && $template[$key]['required'] === 'true' && $value === null) {
                throw new RequiredDataEmptyException('A required field is empty');
            }
        }
    }
}
