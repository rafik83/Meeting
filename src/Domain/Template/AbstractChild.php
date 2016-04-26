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
     * @return array
     */
    abstract public function normalize();
}
