<?php

namespace Proximum\Vimeet\Application\Command\Template\Form;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;

class Save implements Command
{
    /** @var FormTemplate */
    public $template;

    /** @var array */
    public $value;

    public function __construct(FormTemplate $template, array $value)
    {
        $this->template = $template;
        $this->value = $value;
    }
}
