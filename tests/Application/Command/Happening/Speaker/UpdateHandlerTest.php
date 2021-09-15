<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Speaker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Happening\Speaker\Update;
use Proximum\Vimeet\Application\Command\Happening\Speaker\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Happening\SpeakerTranslation;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\SpeakerRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        //Context
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');
        $user = $this->prophesize(User::class);
        $user->getEmail()->shouldBeCalled()->willReturn('thomas@hotmail.com');

        //Expected
        $expectedSpeaker       = new Speaker($event, 'a', 'b', 'c', '', '', $user->reveal());
        $expectedTranslationFR = new SpeakerTranslation($expectedSpeaker, 'fr', 'ABC');
        $expectedTranslationEN = new SpeakerTranslation($expectedSpeaker, 'en', 'DEF');
        $expectedSpeaker->getTranslations()->set('fr', $expectedTranslationFR);
        $expectedSpeaker->getTranslations()->set('en', $expectedTranslationEN);

        //Command
        $speaker       = new Speaker($event, 'firstName', 'lastName', 'orga', '', '', $user->reveal());
        $translationFR = new SpeakerTranslation($speaker, 'fr', 'foo');
        $translationEN = new SpeakerTranslation($speaker, 'en', 'bar');
        $speaker->getTranslations()->set('fr', $translationFR);
        $speaker->getTranslations()->set('en', $translationEN);

        $update = new Update($speaker);
        $update->firstname    = 'a';
        $update->lastname     = 'b';
        $update->organization = 'c';
        $update->translations = [
            'fr' => [
                'position' => 'ABC',
            ],
            'en' => [
                'position' => 'DEF',
            ],
        ];
        $update->email      = 'thomas@hotmail.com';

        //Mock
        $speakerRepository = $this->prophesize(SpeakerRepositoryInterface::class);
        $speakerRepository->set($expectedSpeaker)->shouldBeCalled();

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEventAndEmail($event, 'thomas@hotmail.com')->shouldBeCalled()->willReturn($user->reveal());

        $fileStorage = $this->prophesize(FileStorageInterface::class);

        //Handler
        $handler = new UpdateHandler($speakerRepository->reveal(), $fileStorage->reveal(), $userRepository->reveal());
        $handler->handle($update);
    }
}
