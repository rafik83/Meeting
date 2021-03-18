<?php

namespace Proximum\Vimeet\Application\View\Operator;

class OperatorListView
{
    /** @var OperatorView[] */
    public $operatorViews;

    public function __construct(array $operatorViews = [])
    {
        $this->operatorViews = $operatorViews;
    }
}
