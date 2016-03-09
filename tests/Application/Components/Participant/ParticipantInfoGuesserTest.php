<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Participant;

use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Application\Components\Template\TaggedInfoGuesser;
use Proximum\Vimeet\Application\Components\Template\TemplateFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

class ParticipantInfoGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testGuessParticipantLastNameWithEmptyData()
    {
        $user        = new User('test@test.fr', 'test', 'test', 'fr');
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], [], new \DateTime());
        $participant = new Participant($sheet, $user, [], true, true);

        $participantInfoGuesser = new ParticipantInfoGuesser(new TaggedInfoGuesser(new TemplateFactory()));

        $resultParticipant = $participantInfoGuesser->guessParticipantLastName($participant);

        $this->assertEquals('', $resultParticipant);
    }

    public function testGuessParticipantLastNameWithEmptyTemplate()
    {
        $user        = new User('test@test.fr', 'test', 'test', 'fr');
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], [], new \DateTime());
        $participant = new Participant($sheet, $user, [], true, true);
        $participant->setData([
            '563caf1d9b1cb' => 'DUPOND',
            '563caf2746398' => 'Jean',
            '563caf2f0ddbd' => '0909090909',
        ]);

        $participantInfoGuesser = new ParticipantInfoGuesser(new TaggedInfoGuesser(new TemplateFactory()));

        $resultParticipant = $participantInfoGuesser->guessParticipantLastName($participant);

        $this->assertEquals('', $resultParticipant);
    }

    public function testGuessParticipantLastName()
    {
        $user        = new User('test@test.fr', 'test', 'test', 'fr');
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], [], new \DateTime());
        $participant = new Participant($sheet, $user, [], true, true);
        $participant->setData([
            '563caf1d9b1cb' => 'DUPOND',
            '563caf2746398' => 'Jean',
            '563caf2f0ddbd' => '0909090909',
        ]);

        $sheet->getType()->setParticipantTemplate([
            '563caf1d9b1cb' => [
                'type'     => 'lib_text',
                'tags'     => ['participant_firstname'],
                'required' => true,
                'private'  => false,
                'position' => 0,
                'label'    => [
                    'fr' => 'Nom',
                    'en' => 'Lastname'
                ],
                
            ],
            '563caf2746398' => [
                'type'     => 'lib_text',
                'tags'     => ['participant_lastname'],
                'required' => true,
                'private'  => false,
                'position' => 1,
                'label' => [
                    'fr' => 'Prénom',
                    'en' => 'Firstname',
                ],
            ],
            '563caf2f0ddbd' => [
                'type'     => 'lib_text',
                'required' => true,
                'private'  => true,
                'position' => 2,
                'label' => [
                    'fr' => 'Téléphone',
                    'en' => 'Phone',
                ],
            ],
        ]);

        $participantInfoGuesser = new ParticipantInfoGuesser(new TaggedInfoGuesser(new TemplateFactory()));

        $resultParticipant = $participantInfoGuesser->guessParticipantLastName($participant);

        $this->assertEquals('DUPOND', $resultParticipant);
    }

    public function testGuessParticipantFirstNameWithEmptyData()
    {
        $user        = new User('test@test.fr', 'test', 'test', 'fr');
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], [], new \DateTime());
        $participant = new Participant($sheet, $user, [], true, true);

        $participantInfoGuesser = new ParticipantInfoGuesser(new TaggedInfoGuesser(new TemplateFactory()));

        $resultParticipant = $participantInfoGuesser->guessParticipantFirstName($participant);

        $this->assertEquals('', $resultParticipant);
    }

    public function testGuessParticipantFirstNameWithEmptyTemplate()
    {
        $user        = new User('test@test.fr', 'test', 'test', 'fr');
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], [], new \DateTime());
        $participant = new Participant($sheet, $user, [], true, true);
        $participant->setData([
            '563caf1d9b1cb' => 'DUPOND',
            '563caf2746398' => 'Jean',
            '563caf2f0ddbd' => '0909090909',
        ]);

        $participantInfoGuesser = new ParticipantInfoGuesser(new TaggedInfoGuesser(new TemplateFactory()));

        $resultParticipant = $participantInfoGuesser->guessParticipantFirstName($participant);

        $this->assertEquals('', $resultParticipant);
    }

    public function testGuessParticipantFirstName()
    {
        $user        = new User('test@test.fr', 'test', 'test', 'fr');
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], [], new \DateTime());
        $participant = new Participant($sheet, $user, [], true, true);
        $participant->setData([
            '563caf1d9b1cb' => 'DUPOND',
            '563caf2746398' => 'Jean',
            '563caf2f0ddbd' => '0909090909',
        ]);

        $sheet->getType()->setParticipantTemplate([
            '563caf1d9b1cb' => [
                'type'     => 'lib_text',
                'tags'     => ['participant_firstname'],
                'required' => true,
                'private'  => false,
                'position' => 0,
                'label' => [
                    'fr' => 'Nom',
                    'en' => 'Lastname'
                ],
            ],
            '563caf2746398' => [
                'type'     => 'lib_text',
                'tags'     => ['participant_lastname'],
                'required' => true,
                'private'  => false,
                'position' => 1,
                'label' => [
                    'fr' => 'Prénom',
                    'en' => 'Firstname',
                ],
            ],
            '563caf2f0ddbd' => [
                'type'     => 'lib_text',
                'required' => true,
                'private'  => true,
                'position' => 2,
                'label' => [
                    'fr' => 'Téléphone',
                    'en' => 'Phone',
                ],
            ],
        ]);

        $participantInfoGuesser = new ParticipantInfoGuesser(new TaggedInfoGuesser(new TemplateFactory()));

        $resultParticipant = $participantInfoGuesser->guessParticipantFirstName($participant);

        $this->assertEquals('Jean', $resultParticipant);
    }

    public function testGuessParticipantInfoEmpty()
    {
        $user        = new User('test@test.fr', 'test', 'test', 'fr');
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], [], new \DateTime());
        $participant = new Participant($sheet, $user, [], true, true);

        $participantInfoGuesser = new ParticipantInfoGuesser(new TaggedInfoGuesser(new TemplateFactory()));

        $resultParticipant = $participantInfoGuesser->guessParticipantInfo($participant);

        $this->assertEquals('#0', $resultParticipant);
    }

    public function testGuessParticipantInfo()
    {
        $user        = new User('test@test.fr', 'test', 'test', 'fr');
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], [], new \DateTime());
        $participant = new Participant($sheet, $user, [], true, true);
        $participant->setData([
            '563caf1d9b1cb' => 'DUPOND',
            '563caf2746398' => 'Jean',
            '563caf2f0ddbd' => '0909090909',
        ]);

        $sheet->getType()->setParticipantTemplate([
            '563caf1d9b1cb' => [
                'type'     => 'lib_text',
                'tags'     => ['participant_firstname'],
                'required' => true,
                'private'  => false,
                'position' => 0,
                'label' => [
                    'fr' => 'Nom',
                    'en' => 'Lastname'
                ],
            ],
            '563caf2746398' => [
                'type'     => 'lib_text',
                'tags'     => ['participant_lastname'],
                'required' => true,
                'private'  => false,
                'position' => 1,
                'label' => [
                    'fr' => 'Prénom',
                    'en' => 'Firstname',
                ],
            ],
            '563caf2f0ddbd' => [
                'type'     => 'lib_text',
                'required' => true,
                'private'  => true,
                'position' => 2,
                'label' => [
                    'fr' => 'Téléphone',
                    'en' => 'Phone',
                ],
            ],
        ]);

        $participantInfoGuesser = new ParticipantInfoGuesser(new TaggedInfoGuesser(new TemplateFactory()));

        $resultParticipant = $participantInfoGuesser->guessParticipantInfo($participant);

        $this->assertEquals('DUPOND Jean', $resultParticipant);
    }
}
