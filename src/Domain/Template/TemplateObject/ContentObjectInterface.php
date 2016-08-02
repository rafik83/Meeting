<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

interface ContentObjectInterface
{
    /**
     * @return string
     */
    public function getContentValue();

    /**
     * @return string
     */
    public function getContentLabel();

    /**
     * @param string $value
     */
    public function setContentValue($value);
}
