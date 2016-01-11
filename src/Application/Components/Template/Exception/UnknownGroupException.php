<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Exception;

class UnknownGroupException extends TemplateException
{
    /**
     * UnknownGroupException constructor.
     *
     * @param string $unknowGroup
     * @param array  $availableGroups
     */
    public function __construct($unknowGroup, array $availableGroups)
    {
        parent::__construct(sprintf('Unknow group "%s", available groups are "%s"', $unknowGroup, implode('", "', $availableGroups)));
    }
}
