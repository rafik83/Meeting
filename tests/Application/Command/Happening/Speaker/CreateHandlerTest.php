<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Happening\Speaker\Create;
use Proximum\Vimeet\Application\Command\Happening\Speaker\CreateHandler;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Happening\SpeakerTranslation;
use Proximum\Vimeet\Domain\Repository\Happening\SpeakerRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        //Context
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        //Expected
        $expectedSpeaker = new Speaker($event, 'toto', 'tutu', 'orga', '', '');
        $translationFR   = new SpeakerTranslation($expectedSpeaker, 'fr', 'foo');
        $translationEN   = new SpeakerTranslation($expectedSpeaker, 'en', 'bar');
        $expectedSpeaker->getTranslations()->set('fr', $translationFR);
        $expectedSpeaker->getTranslations()->set('en', $translationEN);

        //Command
        $create = new Create($event);
        $create->firstname    = 'toto';
        $create->lastname     = 'tutu';
        $create->organization = 'orga';
        $create->translations = [
            'fr' => [
                'position' => 'foo',
            ],
            'en' => [
                'position' => 'bar',
            ]
        ];

        //Mock
        $speakerRepository = $this->prophesize(SpeakerRepositoryInterface::class);
        $speakerRepository->add($expectedSpeaker)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);

        //Handler
        $handler = new CreateHandler($speakerRepository->reveal(), $fileStorage->reveal());
        $handler->handle($create);
    }
}
