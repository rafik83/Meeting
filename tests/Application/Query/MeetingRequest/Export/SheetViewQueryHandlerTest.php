<?php

namespace Proximum\Vimeet\Tests\Application\Query\MeetingRequest\Export;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Planning\ParticipantInfoGuesserCache;
use Proximum\Vimeet\Application\Query\MeetingRequest\Export\SheetViewQuery;
use Proximum\Vimeet\Application\Query\MeetingRequest\Export\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\MeetingRequest\Export\SheetView;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class SheetViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $participantInfoGuesser;

    public function setUp()
    {
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesserCache::class);
    }

    public function testHandleWithoutParticipant()
    {
        $type = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->willReturn($type->reveal());
        $sheet->getId()->willReturn(12);
        $sheet->getTitle()->willReturn('sheet title');
        $type->getCategories()->willReturn(new ArrayCollection([]));
        $type->getTitle('fr')->willReturn('type title');

        $sheetViewQueryHandler = new SheetViewQueryHandler($this->participantInfoGuesser->reveal());
        $result = $sheetViewQueryHandler->handle(new SheetViewQuery($sheet->reveal(), [], 'fr'));

        $expected = new SheetView(
            12,
            'sheet title',
            'type title',
            '',
            [],
            []
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithoutCategories()
    {
        $type = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->willReturn($type->reveal());
        $sheet->getId()->willReturn(12);
        $sheet->getTitle()->willReturn('sheet title');
        $type->getCategories()->willReturn(new ArrayCollection([]));
        $type->getTitle('fr')->willReturn('type title');

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant1->getId()->willReturn(18);
        $participant2->getId()->willReturn(43);

        $this->participantInfoGuesser
            ->guessParticipantCompleteName($participant1, 'fr')
            ->shouldBeCalled()
            ->willReturn('participant 1')
        ;

        $this->participantInfoGuesser
            ->guessParticipantCompleteName($participant2, 'fr')
            ->shouldBeCalled()
            ->willReturn('participant 2')
        ;

        $participants = [
            $participant1->reveal(),
            $participant2->reveal(),
        ];

        $sheetViewQueryHandler = new SheetViewQueryHandler($this->participantInfoGuesser->reveal());
        $result = $sheetViewQueryHandler->handle(new SheetViewQuery($sheet->reveal(), $participants, 'fr'));

        $expected = new SheetView(
            12,
            'sheet title',
            'type title',
            '',
            [18, 43],
            ['participant 1', 'participant 2']
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandle()
    {
        $type = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->willReturn($type->reveal());
        $sheet->getId()->willReturn(12);
        $sheet->getTitle()->willReturn('sheet title');
        $category1 = $this->prophesize(Category::class);
        $category2 = $this->prophesize(Category::class);
        $category1->getTitle('fr')->willReturn('category 1');
        $type->getCategories()->willReturn(new ArrayCollection([$category1->reveal(), $category2->reveal()]));
        $type->getTitle('fr')->willReturn('type title');

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant1->getId()->willReturn(18);
        $participant2->getId()->willReturn(43);

        $this->participantInfoGuesser
            ->guessParticipantCompleteName($participant1, 'fr')
            ->shouldBeCalled()
            ->willReturn('participant 1')
        ;

        $this->participantInfoGuesser
            ->guessParticipantCompleteName($participant2, 'fr')
            ->shouldBeCalled()
            ->willReturn('participant 2')
        ;

        $participants = [
            $participant1->reveal(),
            $participant2->reveal(),
        ];

        $sheetViewQueryHandler = new SheetViewQueryHandler($this->participantInfoGuesser->reveal());
        $result = $sheetViewQueryHandler->handle(new SheetViewQuery($sheet->reveal(), $participants, 'fr'));

        $expected = new SheetView(
            12,
            'sheet title',
            'type title',
            'category 1',
            [18, 43],
            ['participant 1', 'participant 2']
        );

        $this->assertEquals($expected, $result);
    }
}
