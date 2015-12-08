<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Product\Products;

abstract class AbstractRequiredDescriptionTypeUnitPriceOptions extends AbstractProduct
{
    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getDescription($locale)
    {
        return isset($this->options['description'][$locale]) ? $this->options['description'][$locale] : null;
    }

    /**
     * @return string
     */
    public function getRequired()
    {
        return $this->options['required'];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->options['unitPrice'];
    }
}
