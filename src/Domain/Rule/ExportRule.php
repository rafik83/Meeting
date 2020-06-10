<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Rule;

class ExportRule
{
    /** @var int|null */
    private $phoneAccessMinEvaluation;

    /** @var int|null */
    private $emailAccessMinEvaluation;

    public function __construct(?int $phoneAccessMinEvaluation, ?int $emailAccessMinEvaluation)
    {

        $this->phoneAccessMinEvaluation = $phoneAccessMinEvaluation;
        $this->emailAccessMinEvaluation = $emailAccessMinEvaluation;
    }

    public function isPhoneVisible(?int $evaluation): bool
    {
        return $this->phoneAccessMinEvaluation === null || ($evaluation !== null && $evaluation > $this->phoneAccessMinEvaluation);
    }

    public function isEmailVisible(?int $evaluation): bool
    {
        return $this->emailAccessMinEvaluation === null || ($evaluation !== null && $evaluation > $this->emailAccessMinEvaluation);
    }
}
