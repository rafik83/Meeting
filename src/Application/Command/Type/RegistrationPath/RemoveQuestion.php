<?php

namespace Proximum\Vimeet\Application\Command\Type\RegistrationPath;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;

class RemoveQuestion implements Command
{
    /** @var Question */
    public $question;

    public function __construct(Question $question)
    {
        $this->question = $question;
    }
}
