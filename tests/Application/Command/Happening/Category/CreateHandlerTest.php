<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Category;

use Proximum\Vimeet\Application\Command\Happening\Category\Create;
use Proximum\Vimeet\Application\Command\Happening\Category\CreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\CategoryTranslation;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = new Event();
        $event->setLocales(['fr', 'en']);

        $expectedCategory     = new Category($event, 'picto1', 3);
        $expectedTranslation1 = new CategoryTranslation($expectedCategory, 'fr', 'truc');
        $expectedTranslation2 = new CategoryTranslation($expectedCategory, 'en', 'trac');
        $expectedCategory->setTranslation($expectedTranslation1);
        $expectedCategory->setTranslation($expectedTranslation2);

        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $categoryRepository->add($expectedCategory)->shouldBeCalled();

        $create  = new Create($event);
        $create->picto = 'picto1';
        $create->position = 3;
        $create->translations = [
            'fr' => ['title' => 'truc'],
            'en' => ['title' => 'trac'],
        ];

        $handler = new CreateHandler($categoryRepository->reveal());
        $handler->handle($create);
    }
}
