<?php

namespace Proximum\Vimeet\Tests\Application\Components\Visio;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Visio\CreateVisioSettings;
use Proximum\Vimeet\Application\Command\Visio\CreateVisioSettingsHandler;
use Proximum\Vimeet\Application\Components\Visio\VisioSettingsRetriever;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;
use Proximum\Vimeet\Domain\Repository\Visio\VisioSettingsRepositoryInterface;

class VisioSettingsRetrieverTest extends TestCase
{
    /** @var ObjectProphecy */
    private $visioSettingsRepository, $createVisioSettingsHandler, $visioSettings, $event;

    public function setUp(): void
    {
        $this->visioSettingsRepository = $this->prophesize(VisioSettingsRepositoryInterface::class);
        $this->createVisioSettingsHandler = $this->prophesize(CreateVisioSettingsHandler::class);
        $this->visioSettings = $this->prophesize(VisioSettings::class);
        $this->event = $this->prophesize(Event::class);
    }

    public function testGetUnknown(): void
    {
        $this->visioSettingsRepository
            ->getByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->createVisioSettingsHandler
            ->handle(new CreateVisioSettings($this->event->reveal()))
            ->shouldBeCalled()
            ->willReturn($this->visioSettings->reveal())
        ;

        $retriever = new VisioSettingsRetriever(
            $this->visioSettingsRepository->reveal(),
            $this->createVisioSettingsHandler->reveal()
        );

        $retriever->get($this->event->reveal());
    }

    public function testGet(): void
    {
        $this->visioSettingsRepository
            ->getByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($this->visioSettings->reveal())
        ;

        $this->createVisioSettingsHandler
            ->handle(new CreateVisioSettings($this->event->reveal()))
            ->shouldNotBeCalled()
        ;

        $retriever = new VisioSettingsRetriever(
            $this->visioSettingsRepository->reveal(),
            $this->createVisioSettingsHandler->reveal()
        );

        $retriever->get($this->event->reveal());
    }
}
