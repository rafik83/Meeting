<?php

namespace Proximum\Vimeet\Tests\Application\Leni\BadgeLink;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink\LeniBadgeLinkParametersQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink\LeniBadgeLinkParametersView;
use Proximum\Vimeet\Application\Leni\BadgeLink\AvailableChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class AvailableCheckerTest extends TestCase
{
    public function testNoParameter(): void
    {
        // prepare data
        $leniBadgeLinkParametersQueryHandler = $this->prophesize(LeniBadgeLinkParametersQueryHandler::class);
        $leniBadgeLinkParametersQueryHandler->handle(Argument::any())
            ->shouldBeCalled()
            ->willReturn(null);

        $event = $this->prophesize(Event::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        // run tests
        $availableChecker = new AvailableChecker($leniBadgeLinkParametersQueryHandler->reveal());
        $result = $availableChecker->isSatisfiedBy($sheet->reveal());

        $this->assertFalse($result);
    }

    public function testNotConcernedType(): void
    {
        // prepare data
        $leniBadgeLinkParametersView = new LeniBadgeLinkParametersView('http://foo/%s', [42]);

        $leniBadgeLinkParametersQueryHandler = $this->prophesize(LeniBadgeLinkParametersQueryHandler::class);
        $leniBadgeLinkParametersQueryHandler->handle(Argument::any())
            ->shouldBeCalled()
            ->willReturn($leniBadgeLinkParametersView);

        $type = $this->prophesize(Type::class);
        $type->getId()->shouldBeCalled()->willReturn(43);

        $event = $this->prophesize(Event::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        // run tests
        $availableChecker = new AvailableChecker($leniBadgeLinkParametersQueryHandler->reveal());
        $result = $availableChecker->isSatisfiedBy($sheet->reveal());

        $this->assertFalse($result);
    }

    public function testConcernedType()
    {
        // prepare data
        $leniBadgeLinkParametersView = new LeniBadgeLinkParametersView('http://foo/%s', [42]);

        $leniBadgeLinkParametersQueryHandler = $this->prophesize(LeniBadgeLinkParametersQueryHandler::class);
        $leniBadgeLinkParametersQueryHandler->handle(Argument::any())
            ->shouldBeCalled()
            ->willReturn($leniBadgeLinkParametersView);

        $type = $this->prophesize(Type::class);
        $type->getId()->shouldBeCalled()->willReturn(42);

        $event = $this->prophesize(Event::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        // run tests
        $availableChecker = new AvailableChecker($leniBadgeLinkParametersQueryHandler->reveal());
        $result = $availableChecker->isSatisfiedBy($sheet->reveal());

        $this->assertTrue($result);
    }
}
