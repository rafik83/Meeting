<?php

namespace Proximum\Vimeet\Tests\Domain\SheetGroup;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\SheetGroup\RemoveSheetFromGroupChecker;

class RemoveSheetFromGroupCheckerTest extends TestCase
{
    public function testCanNotRemoveSheetFromGroup()
    {
        $groupManager = $this->prophesize(User::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $group = $this->prophesize(Sheet\Group::class);

        $user->getId()->shouldBeCalled()->willReturn(1);
        $sheet->getGroup()->shouldBeCalled()->willReturn($group);
        $group->getManager()->shouldBeCalled()->willReturn($groupManager);
        $groupManager->getId()->shouldBeCalled()->willReturn(2);
        $sheet->getUsers()->shouldBeCalled()->willReturn([$user, $groupManager]);

        $removeSheetFromGroupChecker = new RemoveSheetFromGroupChecker();

        $this->assertFalse($removeSheetFromGroupChecker->canRemoveSheetFromGroup($sheet->reveal()));
    }

    public function testCanRemoveSheetFromGroup()
    {
        $groupManager = $this->prophesize(User::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $group = $this->prophesize(Sheet\Group::class);

        $user->getId()->shouldBeCalled()->willReturn(1);
        $sheet->getGroup()->shouldBeCalled()->willReturn($group);
        $group->getManager()->shouldBeCalled()->willReturn($groupManager);
        $groupManager->getId()->shouldBeCalled()->willReturn(2);
        $sheet->getUsers()->shouldBeCalled()->willReturn([$user]);

        $removeSheetFromGroupChecker = new RemoveSheetFromGroupChecker();

        $this->assertTrue($removeSheetFromGroupChecker->canRemoveSheetFromGroup($sheet->reveal()));
    }
}
