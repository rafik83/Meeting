<?php

namespace Proximum\Vimeet\Domain\Package\Funnel;

use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartStep;
use Proximum\Vimeet\Domain\Model\Sheet;

class Funnel
{
    /**
     * @var Step[]
     */
    private $steps = [];

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var CartStep
     */
    private $cartStep;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }

    /**
     * @param int $currentIndex
     *
     * @return null|Step
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
     *
     * @throws \Exception
     *
     * @return null|Step
     */
    public function getStep($index)
    {
        if (isset($this->steps[$index])) {
            if ($this->steps[$index]->index === intval($index)) {
                return $this->steps[$index];
            } else {
                throw new \Exception(
                    sprintf('Element found on index %s but with index %s ', $index, $this->steps[$index]->index)
                );
            }
        }

        return null;
    }

    /**
     * @param $type
     *
     * @return null|Step
     */
    public function getStepByType($type)
    {
        foreach ($this->steps as $step) {
            if ($step->type === $type) {
                return $step;
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
     * @return int
     */
    public function countStep()
    {
        return count($this->steps);
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
     * @return null|Step
     */
    public function getCurrentUncompletedStep()
    {
        $steps = array_filter($this->steps, function (Step $step) {
            return false === $step->completed;
        });

        return reset($steps);
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param Cart $cart
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return CartStep
     */
    public function getCartStep()
    {
        return $this->cartStep;
    }

    /**
     * @param CartStep $cartStep
     */
    public function setCartStep($cartStep)
    {
        $this->cartStep = $cartStep;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        if ($this->sheet->hasNotCancelledOrders()) {
            return $this->hasOneCompleted();
        }

        if (null !== $this->cartStep) {
            return $this->countStep() < $this->cartStep->getCurrentStep();
        } elseif (null !== $this->cart) {
            return $this->countStep() < $this->cart->getCurrentStep();
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function hasOneCompleted()
    {
        if (null === $this->cartStep) {
            return false;
        }

        foreach ($this->steps as $step) {
            if (true === $step->completed) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @param Step $step
     *
     * @return bool
     */
    public function isStepAvailable(Step $step)
    {
        $uncompletedStep = $this->getCurrentUncompletedStep();

        if ($this->sheet->hasNotCancelledOrders()) {
            return $this->hasStep($step->index);
        }

        return $step === $uncompletedStep
        || false === $uncompletedStep
        || $step->index <= $uncompletedStep->index;
    }
}
