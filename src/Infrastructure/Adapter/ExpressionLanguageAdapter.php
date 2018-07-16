<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\ExpressionLanguageInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionLanguageAdapter implements ExpressionLanguageInterface
{
    public function evaluate($expression, $values = [])
    {
        $expressionLanguage = new ExpressionLanguage();

        return $expressionLanguage->evaluate($expression, $values);
    }
}
