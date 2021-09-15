<?php

namespace Proximum\Vimeet\Application\Command\Type\RegistrationPath;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;

class RemoveAssignationParticipationType implements Command
{
    /** @var Answer */
    public $answer;

    public function __construct(Answer $answer)
    {
        $this->answer = $answer;
    }
}
