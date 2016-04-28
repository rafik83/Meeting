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
    protected $key;

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
     * @param string $key
     * @param string $type
     * @param array  $config
     */
    public function __construct($key, $type, array $config)
    {
        $this->key    = $key;
        $this->type   = $type;
        $this->config = $config;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getOption($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return array
     */
    abstract public function normalize();
}
