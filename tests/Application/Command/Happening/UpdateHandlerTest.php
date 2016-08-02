<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Happening;

use Proximum\Vimeet\Application\Command\Happening\Update;
use Proximum\Vimeet\Application\Command\Happening\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\CategoryTranslation;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $begin = new \DateTime('2016-01-27 00:00:00');
        $end   = new \DateTime('2016-01-29 00:00:00');

        $newBegin = new \DateTime('2016-01-27 10:00:00');
        $newEnd   = new \DateTime('2016-01-29 19:00:00');

        // Current
        $category        = new Category($event, 'picto1', 0);
        $catTranslation1 = new CategoryTranslation($category, 'fr', 'truc');
        $catTranslation2 = new CategoryTranslation($category, 'en', 'trac');
        $category->setTranslation($catTranslation1);
        $category->setTranslation($catTranslation2);

        $happening             = new Happening($event, $begin, $end, $category);
        $happeningTranslation  = new Happening\HappeningTranslation($happening, 'fr', 'truc', 'bidule');
        $happeningTranslation2 = new Happening\HappeningTranslation($happening, 'en', 'trac', 'machin');

        $happening->setTranslation($happeningTranslation);
        $happening->setTranslation($happeningTranslation2);

        $newCategory        = new Category($event, 'picto3', 0);
        $newCatTranslation1 = new CategoryTranslation($newCategory, 'fr', 'trec');
        $newCatTranslation2 = new CategoryTranslation($newCategory, 'en', 'troc');
        $newCategory->setTranslation($newCatTranslation1);
        $newCategory->setTranslation($newCatTranslation2);


        // Expected
        $expectedSubEvent     = new Happening($event, $newBegin, $newEnd, $newCategory);
        $expectedTranslation  = new Happening\HappeningTranslation($expectedSubEvent, 'fr', 'test', 'ok');
        $expectedTranslation2 = new Happening\HappeningTranslation($expectedSubEvent, 'en', 'tset', 'ko');

        $expectedSubEvent->setTranslation($expectedTranslation);
        $expectedSubEvent->setTranslation($expectedTranslation2);

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningRepository->set($expectedSubEvent)->shouldBeCalled();

        // Command
        $update = new Update($happening);
        $update->category = $newCategory;
        $update->begin = $newBegin;
        $update->end   = $newEnd;
        $update->translations = [
            'fr' => [
                'title' => 'test',
                'description' => 'ok',
            ],
            'en' => [
                'title' => 'tset',
                'description' => 'ko',
            ],
        ];

        $handler = new UpdateHandler($happeningRepository->reveal());
        $handler->handle($update);
    }
}
