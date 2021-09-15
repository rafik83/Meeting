<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchAssignToGroup;
use Proximum\Vimeet\Application\Command\Sheet\BatchAssignToGroupHandler;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\SheetGroup\RemoveSheetFromGroupChecker;

class BatchAssignToGroupHandlerTest extends TestCase
{
    public function testAssignHandle()
    {
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $removeSheetFromGroupChecker = $this->prophesize(RemoveSheetFromGroupChecker::class);
        $translator = $this->prophesize(TranslatorInterface::class);

        $group = $this->prophesize(Sheet\Group::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheet1->getId()->willReturn(1);
        $sheet1->hasGroup()->willReturn(false);

        $sheet2->getId()->willReturn(2);
        $sheet2->hasGroup()->willReturn(false);

        $sheet1->setGroup($group)->shouldBeCalled();
        $sheet2->setGroup($group)->shouldBeCalled();

        $sheetRepository->getSheetsById([1, 2])->shouldBeCalled()->willReturn(
            [
                $sheet1->reveal(),
                $sheet2->reveal(),
            ]
        );

        $sheetRepository->set($sheet1->reveal())->shouldBeCalled();
        $sheetRepository->set($sheet2->reveal())->shouldBeCalled();

        $sheetInfoGuesser->guessSheetTitle($sheet1->reveal(), 'fr')->shouldNotBeCalled();
        $sheetInfoGuesser->guessSheetTitle($sheet2->reveal(), 'fr')->shouldNotBeCalled();

        $handler = new BatchAssignToGroupHandler(
            $sheetRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $removeSheetFromGroupChecker->reveal(),
            $translator->reveal()
        );

        $result = $handler->handle(new BatchAssignToGroup([1, 2], $group->reveal(), 'fr'));

        $expected = new BatchResult(
            [$sheet1->reveal(), $sheet2->reveal()],
            'flash.admin.sheet_batch.assignToGroup.success'
        );

        $this->assertEquals($expected, $result);
    }

    public function testAssignWithIgnoredSheetsHandle()
    {
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $removeSheetFromGroupChecker = $this->prophesize(RemoveSheetFromGroupChecker::class);
        $translator = $this->prophesize(TranslatorInterface::class);

        $group = $this->prophesize(Sheet\Group::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $sheet1->getId()->willReturn(1);
        $sheet1->hasGroup()->willReturn(false);

        $sheet2->getId()->willReturn(2);
        $sheet2->hasGroup()->willReturn(false);

        $sheet3->getId()->willReturn(3);
        $sheet3->hasGroup()->willReturn(true);

        $sheet1->setGroup($group)->shouldBeCalled();
        $sheet2->setGroup($group)->shouldBeCalled();

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn(
            [
                $sheet1->reveal(),
                $sheet2->reveal(),
                $sheet3->reveal(),
            ]
        );

        $sheetRepository->set($sheet1->reveal())->shouldBeCalled();
        $sheetRepository->set($sheet2->reveal())->shouldBeCalled();

        $sheetInfoGuesser->guessSheetTitle($sheet3->reveal(), 'fr')->shouldBeCalled()->willReturn(
            'Flash message with ignored sheets'
        );

        $handler = new BatchAssignToGroupHandler(
            $sheetRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $removeSheetFromGroupChecker->reveal(),
            $translator->reveal()
        );

        $result = $handler->handle(new BatchAssignToGroup([1, 2, 3], $group->reveal(), 'fr'));

        $expected = new BatchResult(
            [$sheet1->reveal(), $sheet2->reveal()],
            'flash.admin.sheet_batch.assignToGroup.ignoredSheets',
            'Flash message with ignored sheets'
        );

        $this->assertEquals($expected, $result);
    }

    public function testUnassignHandle()
    {
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $removeSheetFromGroupChecker = $this->prophesize(RemoveSheetFromGroupChecker::class);
        $translator = $this->prophesize(TranslatorInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheet1->hasGroup()->willReturn(true);
        $sheet2->hasGroup()->willReturn(true);

        $sheetRepository->getSheetsById([1, 2])->shouldBeCalled()->willReturn(
            [
                $sheet1->reveal(),
                $sheet2->reveal(),
            ]
        );

        $sheet1->unassignFromGroup()->shouldBeCalled();
        $sheet2->unassignFromGroup()->shouldBeCalled();

        $sheetRepository->set($sheet1->reveal())->shouldBeCalled();
        $sheetRepository->set($sheet2->reveal())->shouldBeCalled();

        $sheetInfoGuesser->guessSheetTitle($sheet1->reveal(), 'fr')->shouldNotBeCalled();
        $sheetInfoGuesser->guessSheetTitle($sheet2->reveal(), 'fr')->shouldNotBeCalled();

        $removeSheetFromGroupChecker->canRemoveSheetFromGroup($sheet1)->shouldBeCalled()->willReturn(true);
        $removeSheetFromGroupChecker->canRemoveSheetFromGroup($sheet2)->shouldBeCalled()->willReturn(true);

        $handler = new BatchAssignToGroupHandler(
            $sheetRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $removeSheetFromGroupChecker->reveal(),
            $translator->reveal()
        );

        $result = $handler->handle(new BatchAssignToGroup([1, 2], null, 'fr'));

        $expected = new BatchResult(
            [$sheet1->reveal(), $sheet2->reveal()],
            'flash.admin.sheet_batch.unassignFromGroup.success'
        );

        $this->assertEquals($expected, $result);
    }

    public function testUnassignWithIgnoredSheetsHandle()
    {
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $removeSheetFromGroupChecker = $this->prophesize(RemoveSheetFromGroupChecker::class);
        $translator = $this->prophesize(TranslatorInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $sheet1->hasGroup()->willReturn(true);
        $sheet2->hasGroup()->willReturn(true);
        $sheet3->hasGroup()->willReturn(false);

        $sheet1->unassignFromGroup()->shouldBeCalled();
        $sheet2->unassignFromGroup()->shouldNotBeCalled();
        $sheet3->unassignFromGroup()->shouldNotBeCalled();

        $sheetRepository->getSheetsById([1, 2, 3])->shouldBeCalled()->willReturn(
            [
                $sheet1->reveal(),
                $sheet2->reveal(),
                $sheet3->reveal(),
            ]
        );

        $removeSheetFromGroupChecker->canRemoveSheetFromGroup($sheet1)->shouldBeCalled()->willReturn(true);
        $removeSheetFromGroupChecker->canRemoveSheetFromGroup($sheet2)->shouldBeCalled()->willReturn(false);
        $removeSheetFromGroupChecker->canRemoveSheetFromGroup($sheet3)->shouldNotBeCalled();

        $sheetInfoGuesser->guessSheetTitle($sheet1->reveal(), 'fr')->shouldNotBeCalled();
        $sheetInfoGuesser->guessSheetTitle($sheet2->reveal(), 'fr')->shouldBeCalled()->willReturn('Sheet2');
        $sheetInfoGuesser->guessSheetTitle($sheet3->reveal(), 'fr')->shouldBeCalled()->willReturn('Sheet3');

        $translator
            ->transChoice(
                'flash.admin.sheet_batch.unassignFromGroup.sheetNotHaveGroup',
                1,
                ['%sheets%' => 'Sheet3'],
                'flashes'
            )
            ->shouldBeCalled()
            ->willReturn('sheet Not Have Group : Sheet3');

        $translator
            ->transChoice(
                'flash.admin.sheet_batch.unassignFromGroup.sheetCannotBeRemoved',
                1,
                ['%sheets%' => 'Sheet2'],
                'flashes'
            )
            ->shouldBeCalled()
            ->willReturn('sheet can not be removed from group : Sheet2');

        $sheetRepository->set($sheet1->reveal())->shouldBeCalled();
        $sheetRepository->set($sheet2->reveal())->shouldNotBeCalled();
        $sheetRepository->set($sheet3->reveal())->shouldNotBeCalled();

        $handler = new BatchAssignToGroupHandler(
            $sheetRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $removeSheetFromGroupChecker->reveal(),
            $translator->reveal()
        );

        $result = $handler->handle(new BatchAssignToGroup([1, 2, 3], null, 'fr'));

        $expected = new BatchResult(
            [$sheet1->reveal()],
            'flash.admin.sheet_batch.unassignFromGroup.ignoredSheets',
            'sheet Not Have Group : Sheet3, sheet can not be removed from group : Sheet2'
        );

        $this->assertEquals($expected, $result);
    }
}
