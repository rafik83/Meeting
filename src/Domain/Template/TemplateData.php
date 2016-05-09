<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

class TemplateData extends Block
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->normalize();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array_map(function (Object $object) {
            return $object->getData();
        }, $this->getObjects());
    }
}
