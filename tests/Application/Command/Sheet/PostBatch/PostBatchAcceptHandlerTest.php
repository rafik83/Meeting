<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\PostBatch;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchAccept;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchAcceptHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetAcceptedEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchAcceptHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $sheet1 = SheetFactory::create($event);
        $sheet2 = SheetFactory::create($event);
        $sheet3 = SheetFactory::create($event);
        $admin  = new Admin('john@doe.com', 'salt', 'password', 'fr', 'john', 'doh', 'ROLE_ADMIN', new \DateTime());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $sheetIndexer = $this->prophesize(SheetIndexerInterface::class);
        $datetime        = new \DateTime();

        $eventDispatcher->dispatch(
            Events::SHEET_ACCEPTED,
            Argument::type(SheetAcceptedEvent::class)
        )->shouldBeCalledTimes(3);

        $sheetIndexer->updateSheets([$sheet1, $sheet2, $sheet3])->shouldBeCalled();

        $query   = new PostBatchAccept([$sheet1, $sheet2, $sheet3], $admin);
        $handler = new PostBatchAcceptHandler(
            $eventDispatcher->reveal(),
            $datetime,
            $sheetIndexer->reveal()
        );

        $handler->handle($query);
    }
}
