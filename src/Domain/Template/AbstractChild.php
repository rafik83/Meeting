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
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $fallback;

    /**
     * AbstactChild constructor.
     *
     * @param string $type
     * @param array  $config
     * @param string $locale
     * @param string $fallback
     */
    public function __construct($type, array $config, $locale, $fallback)
    {
        $this->type     = $type;
        $this->config   = $config;
        $this->locale   = $locale;
        $this->fallback = $fallback;
    }

    /**
     * @param string      $name
     * @param null|string $locale
     * @param null|string $fallback
     *
     * @return mixed
     */
    public function getOption($name, $locale = null, $fallback = null)
    {
        if (null === $locale) {
            return isset($this->config[$name]) ? $this->config[$name] : null;
        }

        return isset($this->config[$name][$locale])
            ? $this->config[$name][$locale]
            : ($fallback ? $this->getOption($name, $fallback) : null);
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
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * @return string
     */
    public function getStyle()
    {
        return $this->getOption('style');
    }

    /**
     * @return string
     */
    abstract public function getComponent();

    /**
     * @return array
     */
    abstract public function normalize();
}
