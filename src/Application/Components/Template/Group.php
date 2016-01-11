<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template;

use Proximum\Vimeet\Application\Components\Template\Exception\UnknownTypeException;

class Group
{
    /**
     * @var array
     */
    private $type = [];

    /**
     * @param string        $name
     * @param TypeInterface $type
     */
    public function addType($name, TypeInterface $type)
    {
        $this->type[$name] = $type;
    }

    /**
     * @param string $name
     *
     * @return TypeInterface
     * @throws UnknownTypeException
     */
    public function getType($name)
    {
        if (!isset($this->type[$name])) {
            throw new UnknownTypeException($name, array_keys($this->type));
        }

        return $this->type[$name];
    }
}
