<?php

namespace Proximum\Vimeet\Application\Query\Package;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\Funnel;
use Proximum\Vimeet\Domain\Package\Funnel\Step;

class PackageViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Step
     */
    public $currentStep;

    /**
     * @var Funnel
     */
    public $funnel;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Funnel $funnel
     * @param Step   $currentStep
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Funnel $funnel, Step $currentStep, Sheet $sheet, $locale)
    {
        $this->funnel      = $funnel;
        $this->currentStep = $currentStep;
        $this->sheet       = $sheet;
        $this->locale      = $locale;
    }
}
