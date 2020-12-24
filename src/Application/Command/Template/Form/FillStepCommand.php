<?php

namespace Proximum\Vimeet\Application\Command\Template\Form;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\View\Template\Form\BlockStepView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;

class FillStepCommand implements Command
{
    /** @var FormTemplate */
    public $formTemplate;

    /** @var Sheet */
    public $sheet;

    /** @var Participant */
    public $participant;

    /** @var BlockStepView */
    public $blockStepView;

    public function __construct(
        FormTemplate $formTemplate,
        Sheet $sheet,
        Participant $participant,
        BlockStepView $blockStepView
    ) {
        $this->formTemplate = $formTemplate;
        $this->sheet = $sheet;
        $this->participant = $participant;
        $this->blockStepView = $blockStepView;
    }
}
