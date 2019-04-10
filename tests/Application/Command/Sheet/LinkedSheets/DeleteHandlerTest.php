<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\LinkedSheets;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\Delete;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\DeleteHandler;
use Proximum\Vimeet\Application\Criteria\LinkedSheets\AreRemovableLinkedSheetsCriteria;
use Proximum\Vimeet\Domain\Exception\DomainException;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;

class DeleteHandlerTest extends TestCase
{
    public function testException()
    {
        $this->expectException(DomainException::class);

        // prepare data
        $areRemovableLinkedSheetsCriteria = $this->prophesize(AreRemovableLinkedSheetsCriteria::class);
        $linkSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);

        $linkedSheets = $this->prophesize(LinkedSheets::class);

        // prophecies dependencies
        $areRemovableLinkedSheetsCriteria->meetCriteria([$linkedSheets])->shouldBeCalled()
            ->shouldBeCalled()
            ->willReturn([]);

        $linkSheetsRepository->remove(Argument::any())->shouldNotHaveBeenCalled();

        // run tests
        $deleteQuery = new Delete($linkedSheets->reveal());
        $deleteHandler = new DeleteHandler(
            $linkSheetsRepository->reveal(), $areRemovableLinkedSheetsCriteria->reveal()
        );
        $deleteHandler->handle($deleteQuery);
    }

    public function test()
    {
        // prepare data
        $areRemovableLinkedSheetsCriteria = $this->prophesize(AreRemovableLinkedSheetsCriteria::class);
        $linkSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);

        $linkedSheets = $this->prophesize(LinkedSheets::class);

        // prophecies dependencies
        $areRemovableLinkedSheetsCriteria->meetCriteria([$linkedSheets])->shouldBeCalled()
            ->shouldBeCalled()
            ->willReturn([$linkedSheets]);

        $linkSheetsRepository->remove($linkedSheets)->shouldBeCalled();

        // run tests
        $deleteQuery = new Delete($linkedSheets->reveal());
        $deleteHandler = new DeleteHandler(
            $linkSheetsRepository->reveal(), $areRemovableLinkedSheetsCriteria->reveal()
        );
        $deleteHandler->handle($deleteQuery);
    }
}
