<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Product\Products;

use Proximum\Vimeet\Application\Components\Product\Including;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractProduct implements ProductInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Including[]
     */
    protected $includedIn;

    /**
     * @var Including[]
     */
    protected $include;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefined([
            'includedIn',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function including(ProductInterface $includer, ProductInterface $include, $quantity)
    {
        $including = new Including($includer, $include, $quantity);
    }

    /**
     * {@inheritdoc}
     */
    public function addIncludedIn(Including $including)
    {
        $this->includedIn[] = $including;
    }

    /**
     * {@inheritdoc}
     */
    public function addInclude(Including $including)
    {
        $this->include[] = $including;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return array|null
     */
    public function getOptionsIncludedIn()
    {
        return isset($this->options['includedIn']) ? $this->options['includedIn'] : null;
    }

    /**
     * @return ProductInterface[]
     */
    public function getIncludedIn()
    {
        return $this->includedIn;
    }

    /**
     * @return ProductInterface[]
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * @return float|null
     */
    public function getQuantityMin()
    {
        return isset($this->options['quantity'])
        && isset($this->options['quantity']['min'])
            ? $this->options['quantity']['min'] : null;
    }

    /**
     * @return float|null
     */
    public function getQuantityMax()
    {
        return isset($this->options['quantity'])
        && isset($this->options['quantity']['max'])
            ? $this->options['quantity']['max'] : null;
    }

    /**
     * @return float|null
     */
    public function getQuantityRange()
    {
        return isset($this->options['quantity'])
        && isset($this->options['quantity']['range'])
            ? $this->options['quantity']['range'] : 1;
    }
}
