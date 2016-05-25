<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PurchasingFunnel\Model;

use Proximum\Vimeet\Domain\Model\Product;

class Options
{
    /**
     * @var array
     */
    public $labels;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var Product[]
     */
    public $options;

    /**
     * Options constructor.
     *
     * @param array     $labels
     * @param bool      $enabled
     * @param Product[] $options
     */
    public function __construct(array $labels, $enabled, array $options)
    {
        foreach ($options as $option) {
            if (!$option->isOption()) {
                throw new \RuntimeException();
            }
        }

        $this->labels   = $labels;
        $this->enabled  = $enabled;
        $this->options  = $options;
    }

    /**
     * @param string     $locale
     * @param mixed|null $default
     *
     * @return string|null
     */
    public function getLabel($locale, $default = null)
    {
        return isset($this->labels[$locale]) ? $this->labels[$locale] : $default;
    }
}
