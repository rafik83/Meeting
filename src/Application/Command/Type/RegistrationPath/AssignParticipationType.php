<?php

namespace Proximum\Vimeet\Application\Command\Type\RegistrationPath;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;
use Proximum\Vimeet\Domain\Model\Type;

class AssignParticipationType implements Command
{
    /** @var Answer */
    public $answer;

    /** @var Type[] */
    public $types = [];

    public function __construct(Answer $answer)
    {
        $this->answer = $answer;
    }
}
