<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Object;

class TagsCollection extends ItemCollection
{
    /**
     * {@inheritdoc}
     */
    public function __construct($type, array $config, $locale, $fallback)
    {
        parent::__construct($type, $config, $locale, $fallback);
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->getOption('tags');
    }

    /**
     * @return bool
     */
    public function isCollectionEnabled()
    {
        return (bool) $this->getOption('collection');
    }
}
