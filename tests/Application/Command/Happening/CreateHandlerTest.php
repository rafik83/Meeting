<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Happening;

use Proximum\Vimeet\Application\Command\Happening\Create;
use Proximum\Vimeet\Application\Command\Happening\CreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\CategoryTranslation;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = new Event();
        $event->setLocales(['fr', 'en']);

        $begin = new \DateTime('2016-01-27 00:00:00');
        $end   = new \DateTime('2016-01-29 00:00:00');

        // Current
        $category        = new Category($event, 'picto1');
        $catTranslation1 = new CategoryTranslation($category, 'fr', 'truc');
        $catTranslation2 = new CategoryTranslation($category, 'en', 'trac');
        $category->setTranslation($catTranslation1);
        $category->setTranslation($catTranslation2);

        // Expected
        $expectedSubEvent = new Happening($event, $begin, $end, $category);
        $expectedTitleTranslation  = new Happening\TitleTranslation($expectedSubEvent, 'fr', 'truc');
        $expectedTitleTranslation2 = new Happening\TitleTranslation($expectedSubEvent, 'en', 'trac');
        $expectedDescTranslation   = new Happening\DescriptionTranslation($expectedSubEvent, 'fr', 'bidule');
        $expectedDescTranslation2  = new Happening\DescriptionTranslation($expectedSubEvent, 'en', 'machin');

        $expectedSubEvent->setTitleTranslation($expectedTitleTranslation);
        $expectedSubEvent->setTitleTranslation($expectedTitleTranslation2);
        $expectedSubEvent->setDescriptionTranslation($expectedDescTranslation);
        $expectedSubEvent->setDescriptionTranslation($expectedDescTranslation2);

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningRepository->add($expectedSubEvent)->shouldBeCalled();

        // Command
        $create = new Create($event);
        $create->category = $category;
        $create->begin = $begin;
        $create->end   = $end;
        $create->titleTranslations = [
            'fr' => ['title' => 'truc'],
            'en' => ['title' => 'trac'],
        ];
        $create->descriptionTranslations = [
            'fr' => ['description' => 'bidule'],
            'en' => ['description' => 'machin'],
        ];

        $handler = new CreateHandler($happeningRepository->reveal());
        $handler->handle($create);
    }
}
