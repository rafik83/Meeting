<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Library\Choice\Exception;

class LanguageNotFoundException extends \Exception
{
    public function __construct($language, array $defined)
    {
        parent::__construct(sprintf('The language "%s" is not found. Defined languages are "%s".', $language, implode('", "', $defined)));
    }
}
