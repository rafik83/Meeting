<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template;

use Proximum\Vimeet\Application\Components\Template\Exception\InvalidDataException;
use Proximum\Vimeet\Application\Components\Template\Exception\UnknownGroupException;

class Template
{
    /**
     * @var array
     */
    private $groups = [];

    /**
     * @param string $name
     * @param Group  $group
     *
     * @return Template
     */
    public function addGroup($name, Group $group)
    {
        $this->groups[$name] = $group;

        return $this;
    }

    /**
     * @param $name
     *
     * @return Group
     * @throws UnknownGroupException
     */
    public function getGroup($name)
    {
        if (!isset($this->groups[$name])) {
            throw new UnknownGroupException($name, array_keys($this->groups));
        }

        return $this->groups[$name];
    }

    /**
     * Validate data against the template
     *
     * @param array $data
     *
     * @throws InvalidDataException
     */
    public function validateData(array $data)
    {
        if (false) {
            throw new InvalidDataException();
        }
    }
}
