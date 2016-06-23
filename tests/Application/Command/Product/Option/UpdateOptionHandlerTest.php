<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Product\Option;

use Proximum\Vimeet\Application\Command\Product\Option\UpdateOption;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;

class UpdateOptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $product = Product::createOption(new Event(), 'toto', 'toto.jpg', 200, 1, 1, 1, true);
        $command = new UpdateOption($product);

        $this->assertInstanceOf(UpdateOption::class, $command);
    }
}
