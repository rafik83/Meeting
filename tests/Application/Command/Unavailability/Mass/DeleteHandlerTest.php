<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability\Mass;


use Proximum\Vimeet\Application\Command\Unavailability\Mass\Delete;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\DeleteHandler;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DeleteHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event    = EventFactory::createEvent();
        $category = new Category($event, 'picto', 'title', 'leftColor', 'rightColor');
        $begin    = new \DateTime('12/10/2016 10:00');
        $end      = new \DateTime('12/10/2016 12:00');
        $mass     = new Mass($event, $category, 'name', $begin, $end, true);

        // Mock
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->remove($mass)->shouldBeCalled();

        // Delete
        $delete = new Delete($mass);

        // Handler
        $handler = new DeleteHandler($massRepository->reveal());
        $handler->handle($delete);
    }
}
