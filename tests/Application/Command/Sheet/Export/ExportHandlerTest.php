<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\File\PersistContent;
use Proximum\Vimeet\Application\Command\File\PersistContentHandler;
use Proximum\Vimeet\Application\Command\Sheet\Export\Export;
use Proximum\Vimeet\Application\Command\Sheet\Export\ExportHandler;
use Proximum\Vimeet\Application\Command\Sheet\Export\Notify;
use Proximum\Vimeet\Application\Command\Sheet\Export\NotifyHandler;
use Proximum\Vimeet\Application\Query\Sheet\Export\ExportQuery;
use Proximum\Vimeet\Application\Query\Sheet\Export\ExportQueryHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class ExportHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);

        $exportQueryHandler = $this->prophesize(ExportQueryHandler::class);
        $persistContentHandler = $this->prophesize(PersistContentHandler::class);
        $notifyHandler = $this->prophesize(NotifyHandler::class);

        $query = new ExportQuery($event->reveal(), 'fr', [1, 2, 3, 4], true);
        $content = "test;truc\nfoo;bar";
        $exportQueryHandler->handle($query)->shouldBeCalled()->willReturn($content);

        $persistContent = new PersistContent(
            $event->reveal(),
            $content,
            'export_event_sheets_%s_%s.csv'
        );
        $file = $this->prophesize(File::class);
        $persistContentHandler->handle($persistContent)->shouldBeCalled()->willReturn($file);

        $notify = new Notify($event->reveal(), $admin->reveal(), 'fr', $file->reveal());
        $notifyHandler->handle($notify)->shouldBeCalled();

        $command = new Export(
            $event->reveal(),
            $admin->reveal(),
            'fr',
            [1, 2, 3, 4],
            true
        );

        $handler = new ExportHandler(
            $exportQueryHandler->reveal(),
            $persistContentHandler->reveal(),
            $notifyHandler->reveal()
        );

        $handler->handle($command);
    }
}
