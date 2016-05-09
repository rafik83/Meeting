<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Object;

use Proximum\Vimeet\Domain\Template\Object;

class Text extends Object
{
    const TYPE_TITLE = 'title';
    const TYPE_TEXT  = 'text';

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getContent($locale)
    {
        return $this->getOption('content', $locale);
    }

    /**
     * @return bool
     */
    public function isTitle()
    {
        return self::TYPE_TITLE === $this->getOption('type');
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->getOption('type');
    }
}
