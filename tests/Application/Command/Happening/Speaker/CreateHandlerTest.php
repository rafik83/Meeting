<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Speaker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Happening\Speaker\Create;
use Proximum\Vimeet\Application\Command\Happening\Speaker\CreateHandler;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Happening\SpeakerTranslation;
use Proximum\Vimeet\Domain\Repository\Happening\SpeakerRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        //Context
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        //Expected
        $expectedSpeaker = new Speaker($event, 'toto', 'tutu', 'orga', '', '', null);
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
            ],
        ];

        //Mock
        $speakerRepository = $this->prophesize(SpeakerRepositoryInterface::class);
        $speakerRepository->add($expectedSpeaker)->shouldBeCalled();
        $userRepository = $this->prophesize(UserRepositoryInterface::class);

        $fileStorage = $this->prophesize(FileStorageInterface::class);

        //Handler
        $handler = new CreateHandler($speakerRepository->reveal(), $fileStorage->reveal(), $userRepository->reveal());
        $handler->handle($create);
    }
}
