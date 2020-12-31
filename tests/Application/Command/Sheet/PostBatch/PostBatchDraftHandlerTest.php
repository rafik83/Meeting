<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\PostBatch;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchDraft;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchDraftHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetDraftEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchDraftHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $sheet1 = SheetFactory::create($event);
        $sheet2 = SheetFactory::create($event);
        $sheet3 = SheetFactory::create($event);
        $sheet1->setValidationState(Sheet::STATE_VALIDATION_VALIDATED);
        $sheet2->setValidationState(Sheet::STATE_VALIDATION_VALIDATED);
        $sheet3->setValidationState(Sheet::STATE_VALIDATION_VALIDATED);
        $admin = new Admin('john@doe.com', 'salt', 'password', 'fr', 'john', 'doh', 'ROLE_ADMIN', new \DateTime());

        // Mock
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $sheetIndexer    = $this->prophesize(SheetIndexerInterface::class);
        $datetime        = new \DateTime();

        $sheetIndexer->updateSheets([$sheet1, $sheet2, $sheet3])->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::SHEET_VALIDATION_DRAFT,
            Argument::type(SheetDraftEvent::class)
        )->shouldBeCalledTimes(3);

        $query   = new PostBatchDraft([$sheet1, $sheet2, $sheet3], $admin);
        $handler = new PostBatchDraftHandler($eventDispatcher->reveal(), $datetime, $sheetIndexer->reveal());

        $handler->handle($query);
    }
}
