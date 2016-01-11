<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Exception;

class UnknownTypeException extends TemplateException
{
    /**
     * UnknownTypeException constructor.
     *
     * @param string $unknowType
     * @param array  $availableTypes
     */
    public function __construct($unknowType, array $availableTypes)
    {
        parent::__construct(sprintf('Unknow type "%s", available types are "%s"', $unknowType, implode('", "', $availableTypes)));
    }
}
