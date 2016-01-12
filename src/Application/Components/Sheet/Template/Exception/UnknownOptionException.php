<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template\Exception;

class UnknownOptionException extends TemplateException
{
    /**
     * UnknownOptionException constructor.
     *
     * @param string $unknowOption
     * @param array  $availableOptions
     */
    public function __construct($unknowOption, array $availableOptions)
    {
        parent::__construct(sprintf('Unknow option "%s", available options are "%s"', $unknowOption, implode('", "', $availableOptions)));
    }
}
