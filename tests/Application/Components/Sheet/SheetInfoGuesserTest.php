<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\TaggedInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\TemplateFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

class SheetInfoGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testGuessSheetInfoWithEmptyDataAndEmptyOwner()
    {
        $user        = new User('test@test.fr', 'test', 'test', 'fr');
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], []);
        $participant = new Participant($sheet, $user, [], true, true);

        $sheet->getParticipants()->add($participant);

        $expectedSheetInfo = '#';

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantInfo($participant)->shouldBeCalled()->willReturn('');

        $taggedInfoGuesser = new TaggedInfoGuesser(new TemplateFactory());

        $sheetInfoGuesser = new SheetInfoGuesser($taggedInfoGuesser, $participantInfoGuesser->reveal());
        $sheetInfoResult  = $sheetInfoGuesser->guessSheetInfo($sheet);

        $this->assertEquals($expectedSheetInfo, $sheetInfoResult);
    }

    public function testGuessSheetInfoWithEmptyData()
    {
        $user        = new User('test@test.fr', 'test', 'test', 'fr');
        $event       = new Event();
        $type        = new Type($event);
        $sheet       = new Sheet($event, $type, [], []);
        $participant = new Participant($sheet, $user, [], true, true);

        $sheet->getParticipants()->add($participant);

        $expectedSheetInfo = 'DUPONT Jean';

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantInfo($participant)->shouldBeCalled()->willReturn('DUPONT Jean');

        $taggedInfoGuesser = new TaggedInfoGuesser(new TemplateFactory());

        $sheetInfoGuesser = new SheetInfoGuesser($taggedInfoGuesser, $participantInfoGuesser->reveal());
        $sheetInfoResult  = $sheetInfoGuesser->guessSheetInfo($sheet);

        $this->assertEquals($expectedSheetInfo, $sheetInfoResult);
    }

    public function testGuessSheetInfo()
    {
        $event = new Event();
        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], []);
        $type->setSheetTemplate([
            'azer' => [
                'label' => 'foobar',
                'template' => [
                    'azert' => [
                        'label' => 'Organization',
                        'type'  => 'lib_text',
                        'tags'  => ['sheet_organization'],
                    ],
                ],
            ],
        ]);
        $sheet->setData([
            'azer' => [
                'azert' => 'Société Truc'
            ]
        ]);

        $expectedSheetInfo = 'Société Truc';

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantInfo()->shouldNotBeCalled();

        $taggedInfoGuesser = new TaggedInfoGuesser(new TemplateFactory());

        $sheetInfoGuesser = new SheetInfoGuesser($taggedInfoGuesser, $participantInfoGuesser->reveal());
        $sheetInfoResult  = $sheetInfoGuesser->guessSheetInfo($sheet);

        $this->assertEquals($expectedSheetInfo, $sheetInfoResult);
    }
}
