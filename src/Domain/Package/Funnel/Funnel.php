<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Funnel;

class Funnel
{
    /**
     * @var Step[]
     */
    private $steps = [];

    /**
     * @param int $currentIndex
     *
     * @return Step|null
     */
    public function getNextStep($currentIndex)
    {
        if (!count($this->steps) < $currentIndex) {
            foreach ($this->steps as $step) {
                if (($currentIndex + 1) === $step->index) {
                    return $step;
                }
            }
        }

        return null;
    }

    /**
     * @param Step $step
     *
     * @return Funnel
     */
    public function addStep(Step $step)
    {
        $this->steps[$step->index] = $step;

        return $this;
    }

    /**
     * @param $index
     * @return Step|null
     *
     * @throws \Exception
     */
    public function getStep($index)
    {
        if (isset($this->steps[$index])) {
            if ($this->steps[$index]->index === intval($index)) {
                return $this->steps[$index];
            } else {
                throw new \Exception(sprintf('Element found on index %s but with index %s ', $index, $this->steps[$index]->index));
            }
        }

        return null;
    }

    /**
     * @return Step[]
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @param int $index
     *
     * @return bool
     */
    public function hasStep($index)
    {
        return null !== $this->getStep(intval($index));
    }

    /**
     * @return Step|null
     */
    public function getCurrentUncompletedStep()
    {
        $steps = array_filter($this->steps, function (Step $step) {
            return $step->completed === false;
        });

        return reset($steps);
    }
}
