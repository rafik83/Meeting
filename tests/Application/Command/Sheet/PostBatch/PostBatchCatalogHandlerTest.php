<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\PostBatch;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalogHandler;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchCatalog;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchCatalogHandler;
use Proximum\Vimeet\Application\Components\Sheet\Request\EnableDisableManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetCatalogEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchCatalogHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $sheet1 = SheetFactory::create($event);
        $sheet1->setInCatalog(false);
        $sheet2 = SheetFactory::create($event);
        $sheet2->setInCatalog(false);
        $sheet3 = SheetFactory::create($event);
        $sheet3->setInCatalog(false);
        $admin = new Admin('john@doe.com', 'salt', 'password', 'fr', 'john', 'doh', 'ROLE_ADMIN', new \DateTime());

        // Mock
        $eventDispatcher      = $this->prophesize(EventDispatcherInterface::class);
        $enableDisableManager = $this->prophesize(EnableDisableManager::class);
        $sheetIndexer         = $this->prophesize(SheetIndexerInterface::class);
        $dateTime             = new \DateTime();

        $sheetIndexer->updateSheets([$sheet1, $sheet2, $sheet3])->shouldBeCalled();

        $enableDisableManager
            ->update(Argument::type(Sheet::class), true)
            ->shouldBeCalledTimes(3);

        $eventDispatcher->dispatch(
            Events::SHEET_CATALOG,
            Argument::type(SheetCatalogEvent::class)
        )->shouldBeCalledTimes(3);

        $query   = new PostBatchCatalog([$sheet1, $sheet2, $sheet3], $admin, BatchCatalogHandler::ADD_CATALOG);
        $handler = new PostBatchCatalogHandler(
            $eventDispatcher->reveal(),
            $enableDisableManager->reveal(),
            $dateTime,
            $sheetIndexer->reveal()
        );

        $handler->handle($query);
    }

    /**
     * Not trigger event if sheet in catalog state is not different than command state
     */
    public function testNotTriggerEvent()
    {
        $event  = EventFactory::createEvent();
        $sheet1 = SheetFactory::create($event);
        $sheet1->setInCatalog(false);
        $sheet2 = SheetFactory::create($event);
        $sheet2->setInCatalog(false);
        $sheet3 = SheetFactory::create($event);
        $sheet3->setInCatalog(false);
        $admin = new Admin('john@doe.com', 'salt', 'password', 'fr', 'john', 'doh', 'ROLE_ADMIN', new \DateTime());

        // Mock
        $eventDispatcher      = $this->prophesize(EventDispatcherInterface::class);
        $enableDisableManager = $this->prophesize(EnableDisableManager::class);
        $sheetIndexer         = $this->prophesize(SheetIndexerInterface::class);
        $dateTime             = new \DateTime();

        $sheetIndexer->updateSheets([$sheet1, $sheet2, $sheet3])->shouldBeCalled();

        $enableDisableManager
            ->update(Argument::type(Sheet::class), false)
            ->shouldBeCalledTimes(3);

        $eventDispatcher->dispatch(
            Events::SHEET_CATALOG,
            Argument::type(SheetCatalogEvent::class)
        )->shouldNotBeCalled();

        $query   = new PostBatchCatalog([$sheet1, $sheet2, $sheet3], $admin, BatchCatalogHandler::REMOVE_CATALOG);
        $handler = new PostBatchCatalogHandler(
            $eventDispatcher->reveal(),
            $enableDisableManager->reveal(),
            $dateTime,
            $sheetIndexer->reveal()
        );

        $handler->handle($query);
    }
}
