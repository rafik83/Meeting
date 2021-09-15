<?php

namespace Proximum\Vimeet\Application\View\Package;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\Funnel;
use Proximum\Vimeet\Domain\Package\Funnel\Step;

class PackageView
{
    /** @var Step */
    public $currentStep;

    /** @var Sheet */
    public $sheet;

    /** @var Funnel */
    public $funnel;

    /** @var AbstractProductsView */
    public $products;

    /** @var bool */
    public $canAddParticipant;

    /**
     * @param AbstractProductsView $productsView
     * @param Sheet                $sheet
     * @param Funnel               $funnel
     * @param Step                 $currentStep
     * @param bool                 $canAddParticipant
     */
    public function __construct(
        AbstractProductsView $productsView,
        Sheet $sheet,
        Funnel $funnel,
        Step $currentStep,
        bool $canAddParticipant = false
    ) {
        $this->products    = $productsView;
        $this->sheet       = $sheet;
        $this->funnel      = $funnel;
        $this->currentStep = $currentStep;
        $this->canAddParticipant = $canAddParticipant;
    }
}
