<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\Visio;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio\EndVisioRedirect;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio\EndVisioRedirectHandler;

class EndVisioRedirectHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $router, $sheet, $meeting, $participant, $type;

    public function setUp(): void
    {
        $this->router = $this->prophesize(RouterInterface::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->meeting = $this->prophesize(Meeting::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->type = $this->prophesize(Type::class);
        $this->sheet->getType()->willReturn($this->type->reveal());
    }

    public function testHandleNoEvaluation(): void
    {
        $this->type->canEvaluateMeeting()->shouldBeCalled()->willReturn(false);

        $this->sheet->getId()->shouldBeCalled()->willReturn(12);
        $this->participant->getId()->shouldBeCalled()->willReturn(11)
        ;

        $this->router
            ->generate(Route::AGENDA_PARTICIPANT, [
                'sheet' => 12,
                'participant' => 11,
            ])
            ->shouldBeCalled()
            ->willReturn('/route/to/agenda')
        ;

        $command = new EndVisioRedirect(
            $this->sheet->reveal(),
            $this->participant->reveal(),
            $this->meeting->reveal()
        );
        $handler = new EndVisioRedirectHandler(
            $this->router->reveal()
        );

        $result = $handler($command);

        $this->assertEquals('/route/to/agenda', $result);
    }

    public function testHandleEvaluation(): void
    {
        $this->type->canEvaluateMeeting()->shouldBeCalled()->willReturn(true);

        $this->sheet->getId()->shouldBeCalled()->willReturn(12);
        $this->meeting->getId()->shouldBeCalled()->willReturn(11)
        ;

        $this->router
            ->generate(Route::MEETING_EVALUATION, [
                'sheet' => 12,
                'meeting' => 11,
            ])
            ->shouldBeCalled()
            ->willReturn('/route/to/meeting/evaluation')
        ;

        $command = new EndVisioRedirect(
            $this->sheet->reveal(),
            $this->participant->reveal(),
            $this->meeting->reveal()
        );
        $handler = new EndVisioRedirectHandler(
            $this->router->reveal()
        );

        $result = $handler($command);

        $this->assertEquals('/route/to/meeting/evaluation', $result);
    }
}
