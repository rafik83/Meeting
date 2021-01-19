<?php

namespace Proximum\Vimeet\Tests\Application\Command\Type;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Type\Index;
use Proximum\Vimeet\Application\Command\Type\IndexHandler;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class IndexHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $sheet = SheetFactory::create($event);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetIndexerInterface = $this->prophesize(SheetIndexerInterface::class);

        $sheetRepository->getByTypes([$type])->shouldBeCalled()->willReturn([$sheet]);
        $sheetIndexerInterface->updateSheets([$sheet])->shouldBeCalled();

        $indexHandler = new IndexHandler($sheetRepository->reveal(), $sheetIndexerInterface->reveal());
        $indexHandler->handle(new Index([$type]));
    }
}
