<?php

namespace Proximum\Vimeet\Domain\Model;

class CartStep
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var int
     */
    private $currentStep;

    /**
     * @param Sheet $sheet
     * @param int   $currentStep
     */
    public function __construct(Sheet $sheet, $currentStep)
    {
        $this->sheet       = $sheet;
        $this->currentStep = $currentStep;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return int
     */
    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    /**
     * @param int $currentStep
     */
    public function setCurrentStep($currentStep)
    {
        $this->currentStep = $currentStep;
    }
}
