<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Event\Archive;
use Proximum\Vimeet\Application\Command\Event\ArchiveHandler;
use Proximum\Vimeet\Application\Command\Event\ArchiveUnArchive;
use Proximum\Vimeet\Application\Command\Event\ArchiveUnArchiveHandler;
use Proximum\Vimeet\Application\Command\Event\UnArchive;
use Proximum\Vimeet\Application\Command\Event\UnArchiveHandler;
use Proximum\Vimeet\Domain\Model\Event;

class ArchiveUnArchiveHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $archiveHandler;

    /** @var ObjectProphecy */
    private $unArchiveHandler;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->archiveHandler = $this->prophesize(ArchiveHandler::class);
        $this->unArchiveHandler = $this->prophesize(UnArchiveHandler::class);
    }

    public function testHandleArchive()
    {
        // Context
        $command = new ArchiveUnArchive($this->event->reveal());
        $command->archive = true;

        // Mock
        $this->archiveHandler
            ->handle(new Archive($this->event->reveal()))
            ->shouldBeCalled()
        ;

        // Handler
        $handler = new ArchiveUnArchiveHandler(
            $this->archiveHandler->reveal(),
            $this->unArchiveHandler->reveal()
        );
        $result = $handler->handle($command);

        $this->assertEquals(ArchiveUnArchive::ARCHIVED, $result);
    }

    public function testHandleUnArchive()
    {
        // Context
        $command = new ArchiveUnArchive($this->event->reveal());
        $command->unArchive = true;

        // Mock
        $this->unArchiveHandler
            ->handle(new UnArchive($this->event->reveal()))
            ->shouldBeCalled()
        ;

        // Handler
        $handler = new ArchiveUnArchiveHandler(
            $this->archiveHandler->reveal(),
            $this->unArchiveHandler->reveal()
        );
        $result = $handler->handle($command);

        $this->assertEquals(ArchiveUnArchive::UN_ARCHIVED, $result);
    }

    public function testHandleNothing()
    {
        // Context
        $command = new ArchiveUnArchive($this->event->reveal());

        // Mock
        $this->archiveHandler->handle(Argument::any())->shouldNotBeCalled();
        $this->unArchiveHandler->handle(Argument::any())->shouldNotBeCalled();

        // Handler
        $handler = new ArchiveUnArchiveHandler(
            $this->archiveHandler->reveal(),
            $this->unArchiveHandler->reveal()
        );
        $result = $handler->handle($command);

        $this->assertEquals(null, $result);
    }
}
