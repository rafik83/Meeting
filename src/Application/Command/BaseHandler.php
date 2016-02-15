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
     * @param array $data
     * @param array $template
     *
     * @throws RequiredDataEmptyException
     */
    public function checkDataConstraint(array $data, array $template)
    {
        $keys = [];

        foreach ($data as $key => $value) {
            if (isset($template[$key])
                && isset($template[$key]['required'])
                && $template[$key]['required'] === true
                && $value === null
            ) {
                $keys[] = $key;
            }
        }

        if (!empty($keys)) {
            throw new RequiredDataEmptyException($keys);
        }
    }
}
