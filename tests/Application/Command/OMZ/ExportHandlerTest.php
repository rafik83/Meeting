<?php

namespace Proximum\Vimeet\Tests\Application\Command\OMZ;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\OMZ\Export;
use Proximum\Vimeet\Application\Command\OMZ\ExportHandler;
use Proximum\Vimeet\Application\Command\OMZ\Notify;
use Proximum\Vimeet\Application\Command\OMZ\NotifyHandler;
use Proximum\Vimeet\Application\Command\OMZ\PersistContent;
use Proximum\Vimeet\Application\Command\OMZ\PersistContentHandler;
use Proximum\Vimeet\Application\Command\OMZ\PrepareContent;
use Proximum\Vimeet\Application\Command\OMZ\PrepareContentHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class ExportHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);

        $prepareContentHandler = $this->prophesize(PrepareContentHandler::class);
        $persistContentHandler = $this->prophesize(PersistContentHandler::class);
        $notifyHandler = $this->prophesize(NotifyHandler::class);

        $content = 'omz;content;csv';
        $file = $this->prophesize(File::class);

        $prepareContentHandler->handle(new PrepareContent($event->reveal()))->shouldBeCalled()->willReturn($content);
        $persistContentHandler->handle(new PersistContent($event->reveal(), $content))
            ->shouldBeCalled()
            ->willReturn($file->reveal())
        ;
        $notifyHandler->handle(new Notify($event->reveal(), $admin->reveal(), $file->reveal()))
            ->shouldBeCalled()
        ;

        $command = new Export($event->reveal(), $admin->reveal());
        $handler = new ExportHandler(
            $prepareContentHandler->reveal(),
            $persistContentHandler->reveal(),
            $notifyHandler->reveal()
        );
        $handler->handle($command);
    }
}
