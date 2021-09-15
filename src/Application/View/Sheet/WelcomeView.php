<?php

namespace Proximum\Vimeet\Application\View\Sheet;

class WelcomeView
{
    /**
     * @var bool
     */
    public $hasPackage = false;

    /**
     * @var bool
     */
    public $hasProgram = false;

    /**
     * @param bool $hasPackage
     * @param bool $hasProgram
     */
    public function __construct(bool $hasPackage, bool $hasProgram)
    {
        $this->hasPackage = $hasPackage;
        $this->hasProgram = $hasProgram;
    }

    /**
     * @return int
     */
    public function getColSize()
    {
        if ($this->hasPackage && $this->hasProgram) {
            return 4;
        }

        if ($this->hasPackage || $this->hasProgram) {
            return 6;
        }

        return 12;
    }
}
