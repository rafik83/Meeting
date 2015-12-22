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
        $this->steps[] = $step;
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
        foreach ($this->steps as $step) {
            if ($step->getKey() === $key) {
                return $step;
            }
        }

        return null;
    }

    /**
     * @param Step $step
     */
    public function removeStep(Step $step)
    {
        $this->steps = array_filter($this->steps, function ($templateStep) use ($step) {
            return $templateStep->getKey() !== $step->getKey();
        });
    }
}
