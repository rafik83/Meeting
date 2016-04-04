<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Library\Admin;

class TypeFactory
{
    /**
     * @var array
     */
    private $types;

    /**
     * TypeFactory constructor.
     *
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * @param $type
     *
     * @return string
     * @throws \Exception
     */
    public function getForm($type)
    {
        if (!array_key_exists($type, $this->types)) {
            throw new \Exception("$type not known");
        }

        return $this->types[$type];
    }
}
