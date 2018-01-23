<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Template;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TaggedInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class ParticipantInfoGuesserTest extends TestCase
{
    /** @var TaggedInfoGuesser */
    private $taggedInfoGuesser;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var User */
    private $user;

    /** @var Event */
    private $event;

    /** @var Type */
    private $type;

    /** @var RegistrationTemplate */
    private $registrationTemplate;

    /** @var Sheet */
    private $sheet;

    /** @var Participant */
    private $participant;

    /** @var TemplateData */
    private $templateData;

    /** @var ObjectProphecy */
    private $locale;

    public function setUp()
    {
        $this->taggedInfoGuesser    = $this->prophesize(TaggedInfoGuesser::class);
        $this->templateDataFactory  = $this->prophesize(TemplateDataFactory::class);
        $this->user                 = UserFactory::create();
        $this->event                = EventFactory::createEvent();
        $this->type                 = new Type($this->event);
        $this->registrationTemplate = new RegistrationTemplate(
            'Registration template',
            [],
            ['fr'],
            'fr',
            new \DateTime('2017-12-01')
        );
        $this->sheet                = SheetFactory::create($this->event, $this->user, new \DateTime(), $this->type);
        $this->participant          = new Participant($this->sheet, $this->user, [], true);
        $this->templateData         = new TemplateData($this->type, [], 'fr', 'fr');
        $this->locale               = 'fr';
    }

    public function testGuessParticipantLastName()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);

        $this->taggedInfoGuesser
            ->guessFirst(
                $this->registrationTemplate,
                $this->participant->getData(),
                Tag::PARTICIPANT_LASTNAME,
                $this->locale
            )
            ->shouldBeCalled()
            ->willReturn('LastName');

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $this->assertEquals('LastName', $guesser->guessParticipantLastName($this->participant, $this->locale));
    }

    public function testGuessParticipantFirstName()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);

        $this->taggedInfoGuesser
            ->guessFirst(
                $this->registrationTemplate,
                $this->participant->getData(),
                Tag::PARTICIPANT_FIRSTNAME,
                $this->locale
            )
            ->shouldBeCalled()
            ->willReturn('FirstName');

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $this->assertEquals('FirstName', $guesser->guessParticipantFirstName($this->participant, $this->locale));
    }

    public function testGuessParticipantInfos()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);
        $tags = Tag::getParticipantTags();
        $this->templateDataFactory
            ->createRegistrationFromParticipant($this->participant, $this->locale)
            ->shouldBeCalled()
            ->willReturn($this->templateData);

        foreach ($tags as $tag) {
            $this->taggedInfoGuesser
                ->guessFirstFromTemplateData(
                    $this->templateData,
                    $tag
                )
                ->shouldBeCalled()
                ->willReturn('foobar_' . $tag);
        }

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $expected = [
            'participant_firstname' => 'foobar_participant_firstname',
            'participant_lastname'  => 'foobar_participant_lastname',
            'participant_phone'     => 'foobar_participant_phone',
            'participant_mobile'    => 'foobar_participant_mobile',
            'participant_position'  => 'foobar_participant_position',
            'participant_avatar'    => 'foobar_participant_avatar',
            'participant_address'   => 'foobar_participant_address',
            'participant_zipcode'   => 'foobar_participant_zipcode',
            'participant_city'      => 'foobar_participant_city',
            'participant_country'   => 'foobar_participant_country',
            'participant_website'   => 'foobar_participant_website',
            'participant_gender'    => 'foobar_participant_gender',
        ];

        $this->assertEquals($expected, $guesser->guessParticipantInfos($this->participant, $this->locale));
    }

    public function testGuessParticipantInfosWithTemplateData()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);
        $tags = Tag::getParticipantTags();

        foreach ($tags as $tag) {
            $this->taggedInfoGuesser
                ->guessFirstFromTemplateData(
                    $this->templateData,
                    $tag
                )
                ->shouldBeCalled()
                ->willReturn('foobar_' . $tag);
        }

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $expected = [
            'participant_firstname' => 'foobar_participant_firstname',
            'participant_lastname'  => 'foobar_participant_lastname',
            'participant_phone'     => 'foobar_participant_phone',
            'participant_mobile'    => 'foobar_participant_mobile',
            'participant_position'  => 'foobar_participant_position',
            'participant_avatar'    => 'foobar_participant_avatar',
            'participant_address'   => 'foobar_participant_address',
            'participant_zipcode'   => 'foobar_participant_zipcode',
            'participant_city'      => 'foobar_participant_city',
            'participant_country'   => 'foobar_participant_country',
            'participant_website'   => 'foobar_participant_website',
            'participant_gender'    => 'foobar_participant_gender',
        ];

        $this->assertEquals($expected, $guesser->guessParticipantInfosWithTemplateData($this->templateData));
    }

    public function testGuessParticipantPhone()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);

        $this->taggedInfoGuesser
            ->guessFirst(
                $this->registrationTemplate,
                $this->participant->getData(),
                Tag::PARTICIPANT_PHONE,
                $this->locale
            )
            ->shouldBeCalled()
            ->willReturn('0123456789');

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $this->assertEquals('0123456789', $guesser->guessParticipantPhone($this->participant, $this->locale));
    }

    public function testGuessParticipantInfoForMail()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);

        $this->templateDataFactory
            ->createRegistrationFromParticipant($this->participant, $this->locale)
            ->shouldBeCalled()
            ->willReturn($this->templateData);

        $mailBuildedInfo = [];
        foreach ($this->templateData->getAllTaggedDatas() as $tag => $values) {
            $mailBuildedInfo[$tag] = (!empty($values)) ? reset($values) : '';
        }

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $this->assertEquals($mailBuildedInfo, $guesser->guessParticipantInfoForMail($this->participant, $this->locale));
    }

    public function testGuessParticipantPosition()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);

        $this->templateDataFactory
            ->createRegistrationFromParticipant($this->participant, $this->locale)
            ->shouldBeCalled()
            ->willReturn($this->templateData);

        $expected = $this->templateData->getTaggedContentValue(Tag::PARTICIPANT_POSITION);

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $this->assertEquals($expected, $guesser->guessParticipantPosition($this->participant, $this->locale));
    }

    public function testGuessParticipantCompleteName()
    {
        $this->type->setRegistrationTemplate($this->registrationTemplate);
        $tags = Tag::getParticipantTags();
        $this->templateDataFactory
            ->createRegistrationFromParticipant($this->participant, $this->locale)
            ->shouldBeCalled()
            ->willReturn($this->templateData);

        foreach ($tags as $tag) {
            $this->taggedInfoGuesser
                ->guessFirstFromTemplateData(
                    $this->templateData,
                    $tag
                )
                ->shouldBeCalled()
                ->willReturn('foobar_' . $tag);
        }

        $guesser = new ParticipantInfoGuesser($this->taggedInfoGuesser->reveal(), $this->templateDataFactory->reveal());

        $expected = 'foobar_participant_firstname foobar_participant_lastname';

        $this->assertEquals($expected, $guesser->guessParticipantCompleteName($this->participant, $this->locale));
    }
}
