<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Command\Tip\Event\Remove;
use Proximum\Vimeet\Application\Command\Tip\Event\RemoveHandler;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\TipFactory;

class RemoveHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $tip = TipFactory::createTip('Awsm tip');

        $remove = new Remove($tip);

        $handler = new RemoveHandler($tipRepository->reveal());

        $tipRepository->removeTipForEvent($tip)->shouldBeCalled();

        $handler->handle($remove);

    }
}
