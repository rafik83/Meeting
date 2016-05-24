<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

abstract class AbstractChild
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * AbstractChild constructor.
     *
     * @param string $type
     * @param array  $config
     */
    public function __construct($type, array $config)
    {
        $this->type   = $type;
        $this->config = $config;
    }

    /**
     * @param string      $name
     * @param null|string $locale
     *
     * @return mixed
     */
    public function getOption($name, $locale = null)
    {
        if (null === $locale) {
            return isset($this->config[$name]) ? $this->config[$name] : null;
        }

        return isset($this->config[$name][$locale]) ? $this->config[$name][$locale] : null;
    }

    /**
     * @param string      $name
     * @param mixed       $value
     * @param null|string $locale
     */
    public function setOption($name, $value, $locale = null)
    {
        if (null === $locale) {
            $this->config[$name] = $value;
        } else {
            $this->config[$name][$locale] = $value;
        }
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    abstract public function normalize();
}
