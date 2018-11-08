<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @param string $locale
     *
     * @return string|null
     */
    public function getContentValueLocalize($locale = null);

    /**
     * @return string
     */
    public function getContentLabel();

    /**
     * @param string|array $value
     */
    public function setContentValue($value);
}
