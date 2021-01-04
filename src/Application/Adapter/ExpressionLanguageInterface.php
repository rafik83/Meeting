<?php

namespace Proximum\Vimeet\Application\Adapter;

interface ExpressionLanguageInterface
{
    /**
     * Evaluate an expression.
     *
     * @param string $expression The expression to compile
     * @param array  $values     An array of values
     *
     * @return string The result of the evaluation of the expression
     */
    public function evaluate(string $expression, array $values = []): string;
}
