<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Happening;

use Proximum\Vimeet\Application\Command\Happening\Update;
use Proximum\Vimeet\Application\Command\Happening\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\CategoryTranslation;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = new Event();
        $event->setLocales(['fr', 'en']);

        $begin = new \DateTime('2016-01-27 00:00:00');
        $end   = new \DateTime('2016-01-29 00:00:00');

        $newBegin = new \DateTime('2016-01-27 10:00:00');
        $newEnd   = new \DateTime('2016-01-29 19:00:00');

        // Current
        $category        = new Category($event, 'picto1');
        $catTranslation1 = new CategoryTranslation($category, 'fr', 'truc');
        $catTranslation2 = new CategoryTranslation($category, 'en', 'trac');
        $category->setTranslation($catTranslation1);
        $category->setTranslation($catTranslation2);

        $happening = new Happening($event, $begin, $end, $category);
        $happeningTitleTranslation  = new Happening\TitleTranslation($happening, 'fr', 'truc');
        $happeningTitleTranslation2 = new Happening\TitleTranslation($happening, 'en', 'trac');
        $happeningDescTranslation   = new Happening\DescriptionTranslation($happening, 'fr', 'bidule');
        $happeningDescTranslation2  = new Happening\DescriptionTranslation($happening, 'en', 'machin');

        $happening->setTitleTranslation($happeningTitleTranslation);
        $happening->setTitleTranslation($happeningTitleTranslation2);
        $happening->setDescriptionTranslation($happeningDescTranslation);
        $happening->setDescriptionTranslation($happeningDescTranslation2);

        $newCategory        = new Category($event, 'picto3');
        $newCatTranslation1 = new CategoryTranslation($newCategory, 'fr', 'trec');
        $newCatTranslation2 = new CategoryTranslation($newCategory, 'en', 'troc');
        $newCategory->setTranslation($newCatTranslation1);
        $newCategory->setTranslation($newCatTranslation2);


        // Expected
        $expectedSubEvent = new Happening($event, $newBegin, $newEnd, $newCategory);
        $expectedTitleTranslation  = new Happening\TitleTranslation($expectedSubEvent, 'fr', 'test');
        $expectedTitleTranslation2 = new Happening\TitleTranslation($expectedSubEvent, 'en', 'tset');
        $expectedDescTranslation   = new Happening\DescriptionTranslation($expectedSubEvent, 'fr', 'ok');
        $expectedDescTranslation2  = new Happening\DescriptionTranslation($expectedSubEvent, 'en', 'ko');

        $expectedSubEvent->setTitleTranslation($expectedTitleTranslation);
        $expectedSubEvent->setTitleTranslation($expectedTitleTranslation2);
        $expectedSubEvent->setDescriptionTranslation($expectedDescTranslation);
        $expectedSubEvent->setDescriptionTranslation($expectedDescTranslation2);

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningRepository->set($expectedSubEvent)->shouldBeCalled();

        // Command
        $update = new Update($happening);
        $update->category = $newCategory;
        $update->begin = $newBegin;
        $update->end   = $newEnd;
        $update->titleTranslations = [
            'fr' => ['title' => 'test'],
            'en' => ['title' => 'tset'],
        ];
        $update->descriptionTranslations = [
            'fr' => ['description' => 'ok'],
            'en' => ['description' => 'ko'],
        ];

        $handler = new UpdateHandler($happeningRepository->reveal());
        $handler->handle($update);
    }
}
