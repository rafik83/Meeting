<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField;

class CommandFactory
{
    /**
     * @var array
     */
    private $types;

    /**
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * @param $type
     *
     * @return mixed
     * @throws \Exception
     */
    public function getCommand($type)
    {
        if (!array_key_exists($type, $this->types)) {
            throw new \Exception("$type not known");
        }

        return $this->types[$type];
    }
}
