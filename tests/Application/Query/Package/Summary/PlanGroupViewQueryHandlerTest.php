<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Package\Summary;

use Proximum\Vimeet\Application\Query\Package\Summary\PlanGroupViewQueryHandler;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQueryHandler;

class PlanGroupViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Mock
        $productViewQueryHandler = $this->prophesize(ProductViewQueryHandler::class);

        $handler = new PlanGroupViewQueryHandler($productViewQueryHandler->reveal());
    }
}
