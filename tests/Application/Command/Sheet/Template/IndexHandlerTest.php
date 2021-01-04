<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Sheet\Template\Index;
use Proximum\Vimeet\Application\Command\Sheet\Template\IndexHandler;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class IndexHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $sheetTemplate = new SheetTemplate(
            'Sheet template',
            [],
            ['fr'],
            'fr',
            new \DateTime(),
            [],
            $event
        );
        $sheet = SheetFactory::create($event);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetIndexerInterface = $this->prophesize(SheetIndexerInterface::class);

        $sheetRepository->getBySheetTemplate($sheetTemplate)->shouldBeCalled()->willReturn([$sheet]);
        $sheetIndexerInterface->updateSheets([$sheet])->shouldBeCalled();

        $indexHandler = new IndexHandler($sheetRepository->reveal(), $sheetIndexerInterface->reveal());
        $indexHandler->handle(new Index($sheetTemplate));
    }
}
