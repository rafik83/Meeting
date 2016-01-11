<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Product;

class Template
{
    /**
     * @var Step[]
     */
    private $steps;

    public function __construct()
    {
        $this->steps = [];
    }

    /**
     * @param Step $step
     */
    public function addStep(Step $step)
    {
        $this->steps[$step->getKey()] = $step;
        $step->setTemplate($this);
    }

    /**
     * @return Step[]
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @param string $key
     *
     * @return null|Step
     */
    public function getStep($key)
    {
        return isset($this->steps[$key]) ? $this->steps[$key] : null;
    }

    /**
     * @param Step $step
     */
    public function removeStep(Step $step)
    {
        unset($this->steps[$step->getKey()]);
    }
}
