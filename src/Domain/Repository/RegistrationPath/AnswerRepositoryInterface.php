<?php

namespace Proximum\Vimeet\Domain\Repository\RegistrationPath;

use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;

interface AnswerRepositoryInterface
{
    public function set(Answer $answer);
}
