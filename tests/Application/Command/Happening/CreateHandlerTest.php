<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Happening\Create;
use Proximum\Vimeet\Application\Command\Happening\CreateHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\CategoryTranslation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $begin = new \DateTime('2016-01-27 00:00:00');
        $end   = new \DateTime('2016-01-29 00:00:00');

        // Current
        $category        = new Category($event, 'picto1', 0, '#AABB56', '#123456');
        $catTranslation1 = new CategoryTranslation($category, 'fr', 'truc');
        $catTranslation2 = new CategoryTranslation($category, 'en', 'trac');
        $category->setTranslation($catTranslation1);
        $category->setTranslation($catTranslation2);
        $type = $this->prophesize(Type::class);

        // Expected
        $expectedSubEvent     = new Happening($event, $begin, $end, $category, [$type->reveal()], true, 10, 'toto');
        $expectedTranslation  = new Happening\HappeningTranslation($expectedSubEvent, 'fr', 'truc', 'bidule');
        $expectedTranslation2 = new Happening\HappeningTranslation($expectedSubEvent, 'en', 'trac', 'machin');

        $expectedSubEvent->setTranslation($expectedTranslation);
        $expectedSubEvent->setTranslation($expectedTranslation2);

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningRepository->add($expectedSubEvent)->shouldBeCalled();

        // Command
        $create                   = new Create($event);
        $create->questionAllowed  = true;
        $create->begin            = $begin;
        $create->category         = $category;
        $create->end              = $end;
        $create->limitParticipant = 10;
        $create->types            = [$type->reveal()];
        $create->translations     = [
            'fr' => [
                'title'       => 'truc',
                'description' => 'bidule',
            ],
            'en' => [
                'title'       => 'trac',
                'description' => 'machin',
            ],
        ];
        $create->invitationCode = 'toto';

        $handler = new CreateHandler($happeningRepository->reveal());
        $handler->handle($create);
    }
}
