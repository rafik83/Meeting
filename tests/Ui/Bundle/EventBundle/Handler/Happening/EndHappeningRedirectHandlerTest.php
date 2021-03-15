<?php


namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\Happening;


use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\EndHappeningRedirect;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\EndHappeningRedirectHandler;

class EndHappeningRedirectHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $router, $sheet, $happening, $participant, $type;

    public function setUp(): void
    {
        $this->router = $this->prophesize(RouterInterface::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->happening = $this->prophesize(Happening::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->type = $this->prophesize(Type::class);
        $this->sheet->getType()->willReturn($this->type->reveal());
    }

    public function testHandleNoEvaluation(): void
    {
        $this->type->canEvaluateHappening()->shouldBeCalled()->willReturn(false);

        $this->sheet->getId()->shouldBeCalled()->willReturn(12);

        $this->router
            ->generate(Route::PROGRAM, [
                'sheet' => 12
            ])
            ->shouldBeCalled()
            ->willReturn('/route/to/program')
        ;

        $command = new EndHappeningRedirect(
            $this->sheet->reveal(),
            $this->happening->reveal()
        );
        $handler = new EndHappeningRedirectHandler(
            $this->router->reveal()
        );

        $result = $handler($command);

        $this->assertEquals('/route/to/program', $result);
    }

    public function testHandleEvaluation(): void
    {
        $this->type->canEvaluateHappening()->shouldBeCalled()->willReturn(true);
        $this->sheet->getId()->shouldBeCalled()->willReturn(12);
        $this->happening->getId()->shouldBeCalled()->willReturn(11)
        ;

        $this->router
            ->generate(Route::HAPPENING_EVALUATION, [
                'sheet' => 12,
                'happening' => 11,
            ])
            ->shouldBeCalled()
            ->willReturn('/route/to/happening/evaluation')
        ;

        $command = new EndHappeningRedirect(
            $this->sheet->reveal(),
            $this->happening->reveal()
        );
        $handler = new EndHappeningRedirectHandler(
            $this->router->reveal()
        );

        $result = $handler($command);

        $this->assertEquals('/route/to/happening/evaluation', $result);
    }
}
