<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability\Category;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Unavailability\Category\Create;
use Proximum\Vimeet\Application\Command\Unavailability\Category\CreateHandler;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Repository\Unavailability\CategoryRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandleWithoutcolorGiven(): void
    {
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setColors('#123123', '#456456', '#AABBCC', '#CCCCCC', '#DDDDDD', '#EEEEEE');

        // Expected
        $expectedCategory = new Category(
            $event,
            'picto',
            'title',
            '#123123',
            '#456456'
        );

        // Mock
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $categoryRepository->create($expectedCategory)->shouldBeCalled();

        $handler       = new CreateHandler($categoryRepository->reveal());
        $create        = new Create($event);
        $create->picto = 'picto';
        $create->title = 'title';

        $handler->handle($create);
    }

    public function testHandleWithAllGiven(): void
    {
        $event = EventFactory::createEvent();

        // Expected
        $expectedCategory = new Category(
            $event,
            'picto',
            'title',
            '#leftColor',
            '#rightColor'
        );

        // Mock
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $categoryRepository->create($expectedCategory)->shouldBeCalled();

        $handler            = new CreateHandler($categoryRepository->reveal());
        $create             = new Create($event);
        $create->picto      = 'picto';
        $create->title      = 'title';
        $create->leftColor  = '#leftColor';
        $create->rightColor = '#rightColor';

        $handler->handle($create);
    }
}
