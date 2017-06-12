<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

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

        $sheet->getGroup()->shouldBeCalled()->willReturn($group);
        $group->getManager()->shouldBeCalled()->willReturn($groupManager);
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

        $sheet->getGroup()->shouldBeCalled()->willReturn($group);
        $group->getManager()->shouldBeCalled()->willReturn($groupManager);
        $sheet->getUsers()->shouldBeCalled()->willReturn([$user]);

        $removeSheetFromGroupChecker = new RemoveSheetFromGroupChecker();

        $this->assertTrue($removeSheetFromGroupChecker->canRemoveSheetFromGroup($sheet->reveal()));
    }
}
