<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Category;

use Proximum\Vimeet\Application\Command\Category\Create;
use Proximum\Vimeet\Application\Command\Category\CreateHandler;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        //Context
        $event = new Event();
        $event->setLocales([]);

        //Expected
        $category = new Category($event);

        //Command
        $create = new Create($event);

        //Mock
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $categoryRepository->add($category);

        //Handler
        $handler = new CreateHandler($categoryRepository->reveal(), $typeRepository->reveal());
        $handler->handle($create);
    }
}
