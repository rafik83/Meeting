<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField;

class CommandHandlerFactory
{
    /**
     * @var array
     */
    private $handlers;

    /**
     * @param array $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param $type
     *
     * @return mixed
     * @throws \Exception
     */
    public function getHandler($type)
    {
        if (!array_key_exists($type, $this->handlers)) {
            throw new \Exception("$type not known");
        }

        return $this->handlers[$type];
    }
}
