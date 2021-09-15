<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\PostBatch;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchRefuse;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchRefuseHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class PostBatchRefuseHandlerTest extends TestCase
{
    public function testHandle()
    {
        $admin = $this->prophesize(Admin::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheetIndexer = $this->prophesize(SheetIndexerInterface::class);
        $sheetIndexer->updateSheets([$sheet1->reveal(), $sheet2->reveal()])->shouldBeCalled();

        $postBatchRefuseHandler = new PostBatchRefuseHandler($sheetIndexer->reveal());
        $postBatchRefuseHandler->handle(new PostBatchRefuse([$sheet1->reveal(), $sheet2->reveal()], $admin->reveal()));
    }
}
