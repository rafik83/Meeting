<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\RegistrationPath;

use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;

interface AnswerRepositoryInterface
{
    public function set(Answer $answer);
}
