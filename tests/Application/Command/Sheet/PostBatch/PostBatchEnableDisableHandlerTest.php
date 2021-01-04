<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\PostBatch;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalog;
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalogHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchEnableDisableHandler;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchEnableDisable;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchEnableDisableHandler;
use Proximum\Vimeet\Application\Components\Sheet\HappeningParticipation\EnableDisableManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetEnableDisableEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchEnableDisableHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $sheet1 = SheetFactory::create($event);
        $sheet2 = SheetFactory::create($event);
        $sheet3 = SheetFactory::create($event);
        $admin  = new Admin('john@doe.com', 'salt', 'password', 'fr', 'john', 'doh', 'ROLE_ADMIN', new \DateTime());

        // Mock
        $batchCatalogHandler  = $this->prophesize(BatchCatalogHandler::class);
        $eventDispatcher      = $this->prophesize(EventDispatcherInterface::class);
        $datetime             = new \DateTime();
        $enableDisableManager = $this->prophesize(EnableDisableManager::class);
        $sheetIndexer = $this->prophesize(SheetIndexerInterface::class);

        $sheetIndexer->updateSheets([$sheet1, $sheet2, $sheet3])->shouldBeCalled();

        $batchCatalogHandler
            ->handle(Argument::type(BatchCatalog::class))
            ->shouldNotBeCalled();

        $enableDisableManager
            ->update(Argument::type(Sheet::class), true)
            ->shouldBeCalledTimes(3);

        $eventDispatcher->dispatch(
            Events::SHEET_ENABLE_DISABLE,
            Argument::type(SheetEnableDisableEvent::class)
        )->shouldBeCalledTimes(3);

        $query   = new PostBatchEnableDisable([$sheet1, $sheet2, $sheet3], [1, 2, 3], $admin, BatchEnableDisableHandler::STATE_ENABLE);
        $handler = new PostBatchEnableDisableHandler(
            $batchCatalogHandler->reveal(),
            $eventDispatcher->reveal(),
            $datetime,
            $enableDisableManager->reveal(),
            $sheetIndexer->reveal()
        );

        $handler->handle($query);
    }

    public function testRemoveFromCatalogHandle()
    {
        $event  = EventFactory::createEvent();
        $sheet1 = SheetFactory::create($event);
        $sheet2 = SheetFactory::create($event);
        $sheet3 = SheetFactory::create($event);
        $admin  = new Admin('john@doe.com', 'salt', 'password', 'fr', 'john', 'doh', 'ROLE_ADMIN', new \DateTime());

        // Mock
        $batchCatalogHandler  = $this->prophesize(BatchCatalogHandler::class);
        $eventDispatcher      = $this->prophesize(EventDispatcherInterface::class);
        $datetime             = new \DateTime();
        $enableDisableManager = $this->prophesize(EnableDisableManager::class);
        $sheetIndexer = $this->prophesize(SheetIndexerInterface::class);

        $sheetIndexer->updateSheets([$sheet1, $sheet2, $sheet3])->shouldBeCalled();

        $batchCatalogHandler
            ->handle(Argument::type(BatchCatalog::class))
            ->shouldBeCalled();

        $enableDisableManager
            ->update(Argument::type(Sheet::class), false)
            ->shouldBeCalledTimes(3);

        $eventDispatcher->dispatch(
            Events::SHEET_ENABLE_DISABLE,
            Argument::type(SheetEnableDisableEvent::class)
        )->shouldBeCalledTimes(3);

        $query   = new PostBatchEnableDisable([$sheet1, $sheet2, $sheet3], [1, 2, 3], $admin, BatchEnableDisableHandler::STATE_DISABLE);
        $handler = new PostBatchEnableDisableHandler(
            $batchCatalogHandler->reveal(),
            $eventDispatcher->reveal(),
            $datetime,
            $enableDisableManager->reveal(),
            $sheetIndexer->reveal()
        );

        $handler->handle($query);
    }
}
