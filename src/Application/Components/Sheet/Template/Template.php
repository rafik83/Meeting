<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\InvalidDataException;
use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownGroupException;

class Template
{
    /**
     * @var Group[]
     */
    private $groups = [];

    /**
     * @param Group $group
     *
     * @return Template
     */
    public function addGroup(Group $group)
    {
        $this->groups[$group->getName()] = $group;

        return $this;
    }

    /**
     * @param $name
     *
     * @throws UnknownGroupException
     * @return Group
     *
     */
    public function getGroup($name = 'default')
    {
        if (!isset($this->groups[$name])) {
            throw new UnknownGroupException($name, array_keys($this->groups));
        }

        return $this->groups[$name];
    }

    /**
     * Get groups
     *
     * @return Group[]
     */
    public function getGroups()
    {
        return $this->groups;
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

    /**
     * @param string $tag
     *
     * @return array
     */
    public function getTypesByTag($tag)
    {
        return array_reduce($this->getGroups(), function (array $carry, Group $group) use ($tag) {
            return array_values(array_merge($carry, array_filter($group->getTypes(), function (TypeInterface $type) use ($tag) {
                return $type->hasTag($tag);
            })));
        }, []);
    }
}
