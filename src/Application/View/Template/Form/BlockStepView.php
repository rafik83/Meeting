<?php

namespace Proximum\Vimeet\Application\View\Template\Form;

use Proximum\Vimeet\Domain\Template\Block;

class BlockStepView
{
    /** @var Block */
    public $block;

    /** @var string */
    public $description;

    /** @var BreadCrumbView */
    public $breadCrumb;

    public function __construct(
        Block $block,
        string $description,
        BreadCrumbView $breadCrumb
    ) {
        $this->block = $block;
        $this->description = $description;
        $this->breadCrumb = $breadCrumb;
    }

    public function getCurrentStepIndex(): int
    {
        return $this->breadCrumb->currentStepIndex;
    }

    public function getTotalNumberOfStep(): int
    {
        return $this->breadCrumb->getTotalNumberOfStep();
    }
}
