<?php

namespace Proximum\Vimeet\Tests\Application\Command\Visio;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Visio\CreateVisioSettings;
use Proximum\Vimeet\Application\Command\Visio\CreateVisioSettingsHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;
use Proximum\Vimeet\Domain\Repository\Visio\VisioSettingsRepositoryInterface;

class CreateVisioSettingsHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $visioSettingsRepository, $event;

    public function setUp(): void
    {
        $this->visioSettingsRepository = $this->prophesize(VisioSettingsRepositoryInterface::class);
        $this->event = $this->prophesize(Event::class);
    }

    public function testHandleAlreadyExist(): void
    {
        $this->event->getLocales()->shouldBeCalled()->willReturn(['fr']);
        $visioSettings = new VisioSettings($this->event->reveal());
        $this->visioSettingsRepository
            ->getByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($visioSettings)
        ;

        $this->visioSettingsRepository
            ->create(Argument::any())
            ->shouldNotBeCalled()
        ;

        $handler = new CreateVisioSettingsHandler($this->visioSettingsRepository->reveal());

        $result = $handler->handle(new CreateVisioSettings($this->event->reveal()));

        $this->assertEquals($visioSettings, $result);
    }

    public function testHandle(): void
    {
        $this->event->getLocales()->shouldBeCalled()->willReturn(['fr']);
        $visioSettings = new VisioSettings($this->event->reveal());
        $this->visioSettingsRepository
            ->getByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->visioSettingsRepository
            ->create($visioSettings)
            ->shouldBeCalled()
        ;

        $handler = new CreateVisioSettingsHandler($this->visioSettingsRepository->reveal());

        $result = $handler->handle(new CreateVisioSettings($this->event->reveal()));

        $this->assertEquals($visioSettings, $result);
    }
}
