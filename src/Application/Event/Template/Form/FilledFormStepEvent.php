<?php

namespace Proximum\Vimeet\Application\Event\Template\Form;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Template\Block;
use Symfony\Component\EventDispatcher;

class FilledFormStepEvent extends EventDispatcher\Event
{
    /** @var FormTemplate */
    public $formTemplate;

    /** @var Participant */
    public $participant;

    /** @var Block */
    public $block;

    public function __construct(FormTemplate $formTemplate, Participant $participant, Block $block)
    {
        $this->formTemplate = $formTemplate;
        $this->participant = $participant;
        $this->block = $block;
    }
}
